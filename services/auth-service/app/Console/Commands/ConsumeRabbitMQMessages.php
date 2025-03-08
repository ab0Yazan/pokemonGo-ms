<?php

namespace App\Console\Commands;

use App\Services\MessageQueueInterface;
use Illuminate\Console\Command;

class ConsumeRabbitMQMessages extends Command
{

    protected $signature = 'rabbitmq:consume';

    protected $description = 'Consume messages from RabbitMQ';

    public function handle(MessageQueueInterface $rabbitMQService)
    {
        $this->info('Waiting for messages. To exit, press CTRL+C');

        $callback = function ($msg) {
            $this->info("Received message: " . $msg->body);
            $data = json_decode($msg->body, true);
            if ($data['event'] === 'user_registered') {
                $this->info("Sending welcome email to: " . $data['data']['email']);
            }
            $msg->ack();
        };

        $rabbitMQService->consume(env('RABBITMQ_QUEUE'), $callback);
        $this->info('good');
    }
}
