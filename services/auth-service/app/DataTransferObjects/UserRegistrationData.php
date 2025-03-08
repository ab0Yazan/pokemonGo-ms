<?php
namespace App\DataTransferObjects;

use Illuminate\Http\Request;

class UserRegistrationData
{
    public function __construct(public string $name, public string $email)
    {}

    public static function fromRequest(Request $request)
    {
        return new self($request->name, $request->email);
    }
}
