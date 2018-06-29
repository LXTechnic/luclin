<?php

namespace Luclin\Foundation;

use Illuminate\Auth\AuthenticationException;

trait ExceptionHandlerTrait
{

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // return response()->json(['message' => $exception->getMessage()], 401);
        return $request->expectsJson()
                    ? response()->json(['message' => $exception->getMessage()], 401)
                    : redirect()->guest(route('login'));
    }

}