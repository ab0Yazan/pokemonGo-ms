<?php

namespace App\Http\Controllers;

use App\Actions\UserCreateAction;
use App\Actions\UserLoginAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRegistrationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends BaseController
{
    public function register(UserRegistrationRequest $request, UserCreateAction $action)
    {
        $data = $request->getUserRegistrationData();
        $userData= $action->execute($data, $request->getEncryptedPassword());
        return $this->sendResponse($userData, 'User register successfully.');
    }


    public function login(LoginRequest $request, UserLoginAction $action)
    {
        try {
            $userData= $action->execute($request->email, $request->password);
            return $this->sendResponse($userData, 'User login successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function verify(Request $request)
    {
        return $this->sendResponse(auth()->user, 'User Data.');
    }
}
