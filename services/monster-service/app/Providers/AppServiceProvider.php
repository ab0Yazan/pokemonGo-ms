<?php

namespace App\Providers;

use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Shared\Services\MessageQueueInterface;
use Shared\Services\RabbitMQService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, 'App\Repositories\UserRepository');
        $this->app->bind(MessageQueueInterface::class, RabbitMQService::class);
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
