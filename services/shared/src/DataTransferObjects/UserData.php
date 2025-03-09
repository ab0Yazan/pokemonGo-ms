<?php

namespace Shared\DataTransferObjects;

class UserData
{
    public function __construct(public int $id, public string $name, public string $email)
    {}

    public static function fromArray(array $data)
    {
        return new self($data['id'], $data['name'], $data['email']);
    }
}
