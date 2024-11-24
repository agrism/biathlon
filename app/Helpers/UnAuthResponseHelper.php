<?php

namespace App\Helpers;

use Illuminate\Http\Response;

class UnAuthResponseHelper
{
    use InstanceTrait;

    public function getResponse(): Response
    {
        return response('<script>window.location.href = "'.route('login').'";</script>');
    }
}
