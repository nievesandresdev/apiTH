<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;

class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $currentPath = $request->path();
        // Rutas que no requieren verificación de API key
        $excludedPaths = [
            'api/stay/hoster/deleteSessionWithApiKey',
            'api/guest/auth/google',
            'api/guest/auth/google/callback',
            'api/guest/auth/facebook',
            'api/guest/auth/facebook/callback',
            'api/guest/auth/facebook/deleteData',
            'test',
            'testEmailPostCheckout',
            'testPrepareYourArrival',
            'testPostCheckin',
            'testEmailGeneral',
            'api/storeRewardStay',
            'testEmailReferent'
        ];

        // Verifica si la ruta actual está en la lista de exclusiones
        if (in_array($currentPath, $excludedPaths)) {
            return $next($request);
        }

        // Verificación de la API key
        $apiKey = $request->header('x-key-api');
        $envApiKey = config('app.x_key_api');

        if (!$apiKey || !$envApiKey || ($apiKey !== $envApiKey)) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED);
        }

        return $next($request);
    }
}
