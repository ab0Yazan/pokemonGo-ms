<?php
namespace App\Actions;

use App\DataTransferObjects\UserData;
use App\Exceptions\AuthenticationException;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Throwable;

class UserLoginAction
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function execute(string $email, string $password): UserData | Throwable
    {
        if(Auth::attempt(['email' => $email, 'password' => $password])){
            $user = Auth::user();
            $token = $this->userRepository->createToken($user);
            return new UserData($user->name, $user->email, $token);
        }
        else{
            throw new AuthenticationException();
        }
    }
}
