<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPException;

class RabbitService
{
    private static $instance = null;
    private $connection;
    private $channel;

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}

    private function connect()
    {
        if ($this->connection && $this->connection->isConnected()) {
            return;
        }
        try {
            $this->connection = new AMQPStreamConnection(
                config('app.rabbitmq.host', 'localhost'),
                config('app.rabbitmq.port', 5672),
                config('app.rabbitmq.user', 'guest'),
                config('app.rabbitmq.password', 'guest'),
                config('app.rabbitmq.vhost', '/'),
                false,      // insistent
                'AMQPLAIN', // login_method
                null,       // login_response
                'en_US',    // locale
                30.0,       // connection_timeout
                130.0,      // read_write_timeout -> Should be > 2 * heartbeat
                null,       // context
                false,      // keepalive
                60          // heartbeat
            );
            $this->channel = $this->connection->channel();
            $this->channel->basic_qos(null, 100, null); // Prefetch 100 messages
            $this->channel->queue_declare(config('app.rabbitmq.queue', 'default_queue'), false, true, false, false);
            $this->channel->exchange_declare(config('app.rabbitmq.exchange', 'default_exchange'), 'direct', false, true, false);
            $this->channel->queue_bind(config('app.rabbitmq.queue', 'default_queue'), config('app.rabbitmq.exchange', 'default_exchange'), config('app.rabbitmq.routing_key', ''));
        } catch (AMQPException $e) {
            Log::error('RabbitMQ connection failed', ['exception' => $e->getMessage()]);
            throw new \Exception("RabbitMQ connection error: " . $e->getMessage());
        }
    }

    public function publishMessage(string $messageBody, string $routingKey = 'acp', string $exchange = null)
    {
        try {
            $exchange = $exchange ?? config('app.rabbitmq.exchange', 'default_exchange');
            $message = new AMQPMessage($messageBody, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
            $this->channel->basic_publish($message, $exchange, $routingKey);
            Log::info('Message published', ['routing_key' => $routingKey, 'exchange' => $exchange]);
            return "Message published with routing key $routingKey";
        } catch (AMQPException $e) {
            Log::error('Failed to publish message', ['exception' => $e->getMessage()]);
            throw new \Exception("Failed to publish message: " . $e->getMessage());
        }
    }

    /**
     * Consumes messages in a batch.
     *
     * @param callable $callback The function to process the batch of messages.
     * @param string|null $queue The queue to consume from.
     * @param int $batchSize The maximum number of messages in a batch.
     * @param int $timeout The maximum time in seconds to wait for a batch to fill up.
     */
    public function consumeBulk(callable $callback, string $queue = null, int $batchSize = 100)
    {
        try {
            $queue = $queue ?? config('app.rabbitmq.queue', 'default_queue');
            $batch = [];

            // Register a shutdown function to process the final partial batch.
            // This ensures messages aren't lost when the worker is gracefully restarted.
            register_shutdown_function(function () use (&$batch, $callback) {
                if (!empty($batch)) {
                    Log::info('Processing final batch on shutdown.', ['size' => count($batch)]);
                    $callback($batch);
                }
            });

            $this->channel->basic_consume(
                $queue,
                '',
                false,
                false, // auto-ack is false
                false,
                false,
                function ($message) use (&$batch, $callback, $batchSize) {
                    $batch[] = $message;
                    if (count($batch) >= $batchSize) {
                        Log::info('Processing full batch.', ['size' => count($batch)]);
                        $callback($batch);
                        $batch = []; // Reset batch
                    }
                }
            );

            // Loop and wait indefinitely for messages.
            // The heartbeat setting on the connection will keep it alive.
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
        } catch (\Exception $e) {
            Log::error('Error consuming messages', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Re-throw the exception to let the command handler know something went wrong.
            throw new \Exception("Error consuming messages: " . $e->getMessage());
        }
    }

    public function close()
    {
        try {
            if ($this->channel && $this->channel->is_open()) $this->channel->close();
            if ($this->connection && $this->connection->isConnected()) $this->connection->close();
        } catch (\Exception $e) {
            Log::error('Error closing RabbitMQ connection', ['exception' => $e->getMessage()]);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}
