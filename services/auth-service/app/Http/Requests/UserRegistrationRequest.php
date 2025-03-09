<?php

namespace App\Http\Requests;

use App\DataTransferObjects\UserRegistrationData;
use Illuminate\Foundation\Http\FormRequest;

class UserRegistrationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'password_confirmation' => 'required|same:password'
        ];
    }

    public function getUserRegistrationData()
    {
        return UserRegistrationData::fromRequest($this);
    }

    public function getEncryptedPassword()
    {
        return bcrypt($this->password);
    }
}
