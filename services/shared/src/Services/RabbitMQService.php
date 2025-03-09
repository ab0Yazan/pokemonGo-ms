<?php

namespace Shared\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use Psr\Log\LoggerInterface;

class RabbitMQService implements MessageQueueInterface
{
    protected $channel;
    protected $queue;
    protected $logger;


    public function __construct(
        protected AMQPStreamConnection $connection
    ) {
        $this->queue = 'events_queue';
        $this->logger = app(LoggerInterface::class);
        $this->connect();
    }

    /**
     * Establish the RabbitMQ connection.
     */
    protected function connect()
    {
        try {
            $this->channel = $this->connection->channel();
            $this->channel->queue_declare($this->queue, false, true, false, false);
            $this->logger->info("Connected to RabbitMQ and declared queue: {$this->queue}");
        } catch (\Exception $e) {
            $this->logger->error("Failed to connect to RabbitMQ: " . $e->getMessage());
            throw $e;
        }
    }

    public function publish(string $message): void
    {
        try {
            $msg = new AMQPMessage($message, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            $this->channel->basic_publish($msg, '', $this->queue);

            $this->logger->info("Message published to queue: {$this->queue}", [
                'message' => $message
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to publish message: " . $e->getMessage());
            throw $e;
        }
    }


    public function consume(callable $callback): void
    {
        try {
            $this->logger->info("Started consuming messages from queue: {$this->queue}");

            $this->channel->basic_consume(
                $this->queue,
                '', // Consumer tag
                false, // No local
                false, // No ack
                false, // Exclusive
                false, // No wait
                $callback
            );

            while (count($this->channel->callbacks)) {
                try {
                    $this->channel->wait();
                } catch (AMQPConnectionClosedException $e) {
                    $this->logger->error("RabbitMQ connection closed: " . $e->getMessage());
                    $this->reconnect();
                } catch (\Exception $e) {
                    $this->logger->error("Error consuming messages: " . $e->getMessage());
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to consume messages: " . $e->getMessage());
            throw $e;
        }
    }


    protected function reconnect()
    {
        $this->logger->info("Reconnecting to RabbitMQ...");

        // Close the existing channel and connection
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }

        // Reconnect
        $this->connect();
    }

    /**
     * Close the RabbitMQ connection and channel.
     */
    public function __destruct()
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
            if ($this->connection) {
                $this->connection->close();
            }

            $this->logger->info("RabbitMQ connection and channel closed.");
        } catch (\Exception $e) {
            $this->logger->error("Failed to close RabbitMQ connection: " . $e->getMessage());
        }
    }
}
