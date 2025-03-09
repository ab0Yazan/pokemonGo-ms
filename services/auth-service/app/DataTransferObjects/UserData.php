<?php
namespace App\DataTransferObjects;
class UserData
{
    public function __construct(public string $name, public string $email, public string $token)
    {}

    public static function fromArray(array $data)
    {
        return new self($data['name'], $data['email'], $data['token']);
    }
}
