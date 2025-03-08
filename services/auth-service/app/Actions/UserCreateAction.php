<?php
namespace App\Actions;

use App\DataTransferObjects\UserData;
use App\DataTransferObjects\UserRegistrationData;
use App\Repositories\UserRepositoryInterface;
use App\Services\MessageQueueInterface;

class UserCreateAction
{
    public function __construct(private UserRepositoryInterface $userRepository, private MessageQueueInterface $rabbitMQService)
    {
    }

    public function execute(UserRegistrationData $data, string $password): UserData
    {
        $user= $this->userRepository->create(['name' => $data->name, 'email' => $data->email, 'password' => $password]);
        $token= $this->userRepository->createToken($user);
        // Publish a message to RabbitMQ
        $message = json_encode([
            'event' => 'user_registered',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
        ]);
        $this->rabbitMQService->publish($message, env('RABBITMQ_QUEUE'));
        return UserData::fromArray(['name' => $user->name, 'email' => $user->email, 'token' => $token]);
    }


}
