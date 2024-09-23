<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        Log::info('setLocale');
        if ($locale = $request->header('Accept-Language')) {
            Log::info('setLocale 1');
            App::setLocale($locale);
            Log::info('setLocale 2');
        }
        Log::info('setLocale 3');
        return $next($request);
    }
}
