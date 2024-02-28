<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Utils\Enums\EnumResponse;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('x-key-api');
        $envApiKey = config('app.x_key_api');

        if (!$apiKey || !$envApiKey || ($apiKey !== $envApiKey)) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED);
        }

        return $next($request);
    }
}
