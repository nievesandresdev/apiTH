<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateEither
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        foreach ($guards as $guard) {
            if (\Auth::guard($guard)->check()) {
                // Define el guard activo para que Auth::user() funcione correctamente
                \Auth::shouldUse($guard);
                return $next($request);
            }
        }
        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
