<?php

namespace App\Providers;

use App\Repositories\UserRepositoryInterface;
use App\Services\MessageQueueInterface;
use App\Services\RabbitMQService;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, 'App\Repositories\UserRepository');
        $this->app->bind(MessageQueueInterface::class, 'App\Services\RabbitMQService');
        $this->app->when(RabbitMQService::class)
        ->needs(AMQPStreamConnection::class)
        ->give(function(){
            return new AMQPStreamConnection(
                env('RABBITMQ_HOST'),
                env('RABBITMQ_PORT'),
                env('RABBITMQ_USER'),
                env('RABBITMQ_PASSWORD')
            );
        }) ;
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
