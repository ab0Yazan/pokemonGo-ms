<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }


    public function find(int $id): ?User
    {
        return User::find($id);
    }


    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function createToken(User $user): string
    {
        return $user->createToken('auth:app')->accessToken;
    }
}
