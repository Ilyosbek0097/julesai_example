<?php

namespace App\Console\Commands;

use App\Services\AccountService;
use App\Services\RabbitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitConsumeMessages extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ in batches continuously';

    public function handle(): void
    {
        Log::info('RabbitMQ bulk consumer started.');
        $this->info('RabbitMQ bulk consumer started.');

        $rabbitmqService = RabbitService::getInstance();
        // The user's code used AccountsHistoryService, but we implemented the logic in AccountService.
        // I will use AccountService as that's the file I created and refactored.
        $accountService = app(AccountService::class);

        try {
            $queueName = 'acpAccSaldo';
            $exchangeName = config('app.rabbitmq.exchange', 'default_exchange'); // Or a specific one if needed
            $routingKey = ''; // Or a specific one

            $this->info("Consumer started for queue: [{$queueName}]");

            $rabbitmqService->consumeBulk(
                function (array $batch) use ($accountService, $queueName) {
                    /** @var AMQPMessage[] $batch */
                    if (empty($batch)) {
                        return;
                    }

                    $dataToUpsert = [];
                    foreach ($batch as $message) {
                        $data = json_decode($message->body, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $dataToUpsert[] = $data;
                        } else {
                            Log::warning("Invalid JSON message received from [{$queueName}], discarding.", ['body' => $message->body]);
                            $message->getChannel()->basic_ack($message->getDeliveryTag());
                        }
                    }

                    if (empty($dataToUpsert)) {
                        return;
                    }

                    $success = $accountService->upsertBulkAccountsHistory($dataToUpsert);

                    $lastMessage = end($batch);
                    $deliveryTag = $lastMessage->getDeliveryTag();
                    $channel = $lastMessage->getChannel();

                    if ($success) {
                        $channel->basic_ack($deliveryTag, true);
                        Log::info("Batch from [{$queueName}] processed and acknowledged.", ['count' => count($batch)]);
                    } else {
                        $channel->basic_ack($deliveryTag, true);
                        Log::error("Batch from [{$queueName}] failed to process, discarding messages.", ['count' => count($batch)]);
                    }
                },
                $queueName,
                20, // Using the smaller batch size we decided on
                $exchangeName,
                $routingKey
            );
        } catch (\Exception $e) {
            Log::error('RabbitMQ consumer encountered a fatal error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->error('RabbitMQ consumer error: ' . $e->getMessage());
        } finally {
            Log::info('Closing RabbitMQ connection.');
            $rabbitmqService->close();
        }
    }
}
