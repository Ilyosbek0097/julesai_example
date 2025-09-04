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
            // Use the new consumeBulk method
            $rabbitmqService->consumeBulk(
                function (array $batch) use ($accountService) {
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
                            Log::warning("Invalid JSON message received, discarding.", ['body' => $message->body]);
                            // Acknowledge invalid message so it's not re-queued
                            $message->getChannel()->basic_ack($message->getDeliveryTag());
                        }
                    }

                    if (empty($dataToUpsert)) {
                        return; // All messages in batch were invalid
                    }

                    // Perform the bulk upsert
                    $success = $accountService->upsertBulkAccountsHistory($dataToUpsert);

                    $lastMessage = end($batch);
                    $deliveryTag = $lastMessage->getDeliveryTag();
                    $channel = $lastMessage->getChannel();

                    if ($success) {
                        // Acknowledge all messages in the batch up to the last one
                        $channel->basic_ack($deliveryTag, true); // true for 'multiple'
                        Log::info('Batch processed and acknowledged successfully.', ['count' => count($batch)]);
                    } else {
                        // On failure, acknowledge the messages anyway to remove them from the queue
                        // and prevent infinite requeue loops, as per user request.
                        $channel->basic_ack($deliveryTag, true); // true for 'multiple'
                        Log::error('Batch failed to process, acknowledging to discard messages.', ['count' => count($batch)]);
                    }
                },
                config('app.rabbitmq.queue', 'default_queue'),
                100 // Batch size
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
