<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Shared\Services\RabbitMQService;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use Shared\DataTransferObjects\UserData;

class ConsumerCommand extends Command
{
    // Command signature and description
    protected $signature = 'rabbitmq:consumer';
    protected $description = 'Consume messages from RabbitMQ';

    // Dependency injection for RabbitMQService and LoggerInterface
    protected RabbitMQService $rabbitMQService;
    protected LoggerInterface $logger;

    /**
     * Constructor to inject dependencies.
     *
     * @param RabbitMQService $rabbitMQService
     * @param LoggerInterface $logger
     */
    public function __construct(RabbitMQService $rabbitMQService, LoggerInterface $logger)
    {
        parent::__construct();
        $this->rabbitMQService = $rabbitMQService;
        $this->logger = $logger;
    }

    /**
     * Handle the command execution.
     *
     * @return int Returns Command::SUCCESS or Command::FAILURE
     */
    public function handle()
    {
        $this->info("Listening for messages on queue: events_queue");

        // Define the callback function to process incoming messages
        $callback = function (AMQPMessage $msg) {
            try {
                $messageBody = $msg->body;
                $this->info("Received message: {$messageBody}");

                // Process the message using custom logic
                $this->processMessage($messageBody);

                // Acknowledge the message to RabbitMQ after successful processing
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

                // Log successful message processing
                $this->logger->info("Message processed successfully", ['message' => $messageBody]);
            } catch (\Exception $e) {
                // Log errors during message processing
                $this->logger->error("Error processing message: " . $e->getMessage(), [
                    'message' => $msg->body
                ]);

                // Reject the message and requeue it for reprocessing
                $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'], false, true);
            }
        };

        // Signal handler for graceful shutdown (e.g., when stopping the consumer)
        $shouldStop = false;
        pcntl_signal(SIGTERM, function () use (&$shouldStop) {
            $shouldStop = true;
        });

        try {
            // Start consuming messages in an infinite loop
            while (!$shouldStop) {
                try {
                    // Begin consuming messages from the queue
                    $this->rabbitMQService->consume($callback);
                } catch (AMQPConnectionClosedException $e) {
                    // Handle RabbitMQ connection errors and attempt to reconnect
                    $this->logger->error("RabbitMQ connection closed: " . $e->getMessage());
                    $this->reconnect();
                } catch (\Exception $e) {
                    // Log any other errors and stop the consumer
                    $this->logger->error("Failed to start consumer: " . $e->getMessage());
                    return Command::FAILURE;
                }

                // Dispatch any pending signals (e.g., SIGTERM for graceful shutdown)
                pcntl_signal_dispatch();
            }

            // Log a message when the consumer stops gracefully
            $this->logger->info("Consumer stopped gracefully.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Log any unexpected errors during consumer execution
            $this->logger->error("Consumer encountered an error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Reconnect to RabbitMQ if the connection is lost.
     */
    protected function reconnect()
    {
        $this->logger->info("Reconnecting to RabbitMQ...");

        // Close the existing RabbitMQ connection and channel
        $this->rabbitMQService->__destruct();

        // Reinitialize the RabbitMQService to establish a new connection
        $this->rabbitMQService->__construct($this->logger);

        $this->logger->info("Reconnected to RabbitMQ successfully.");
    }

    /**
     * Custom message processing logic.
     *
     * @param string $message The message body to process.
     */
    protected function processMessage(string $message)
    {
        $this->info("Processing message: {$message}");
        $data = json_decode($message, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $userData = UserData::fromArray($data['data']);
            User::create([ "id" => $userData->id, "name" => $userData->name ]);
        } else {
            $this->error("Failed to decode message as JSON: {$message}");
        }
    }
}
