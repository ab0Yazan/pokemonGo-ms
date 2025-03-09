<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shared\DataTransferObjects\UserData;
use Shared\Enums\Event;

class UserRegisteredEvent
{
    use Dispatchable, SerializesModels;
    public string $eventId;
    public string $eventType = Event::USER_REGISTERED->value;
    public function __construct(public UserData $data)
    {
        $this->eventId = uniqid('user_registered_', true);
    }
}
