<?php

namespace App\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    public function report(Exception $exception)
    {
        //
    }

    public function render($request, Exception $exception)
    {
        return response()->json([
            'error' => 'Unauthorized',
        ], 401);
    }
}
