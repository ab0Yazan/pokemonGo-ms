<?php

namespace Shared\Services;

interface MessageQueueInterface
{
    public function publish(string $message) : void;
    public function consume(callable $callback) : void;
    public function __destruct();
}
