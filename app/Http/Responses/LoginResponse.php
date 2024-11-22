<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginViewResponse;

class LoginResponse implements LoginViewResponse
{
    public function toResponse($request)
    {
        return view('auth.login');  // Make sure this view exists
    }
}
