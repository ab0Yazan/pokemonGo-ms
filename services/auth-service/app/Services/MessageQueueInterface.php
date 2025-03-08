<?php

namespace App\Services;

interface MessageQueueInterface
{
    public function publish($message, $queue);
    public function consume($queue, $callback);
    public function __destruct();
}
