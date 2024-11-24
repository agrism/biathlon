<?php

namespace App\Http\Middleware;

use App\Helpers\UnAuthResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth()->check()){
            return UnAuthResponseHelper::instance()->getResponse();
        }
        return $next($request);
    }
}
