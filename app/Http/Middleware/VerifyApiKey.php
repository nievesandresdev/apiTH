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
        Log::info('VerifyApiKey ');
        $currentPath = $request->path();
        Log::info('VerifyApiKey 1');
        // Rutas que no requieren verificación de API key
        $excludedPaths = [
            'api/stay/hoster/deleteSessionWithApiKey',
            'test',
        ];
        Log::info('VerifyApiKey 2');
        // Verifica si la ruta actual está en la lista de exclusiones
        if (in_array($currentPath, $excludedPaths)) {
            return $next($request);
        }
        Log::info('VerifyApiKey 3');
        // Verificación de la API key
        $apiKey = $request->header('x-key-api');
        $envApiKey = config('app.x_key_api');
        Log::info('VerifyApiKey 4');
        if (!$apiKey || !$envApiKey || ($apiKey !== $envApiKey)) {
            return bodyResponseRequest(EnumResponse::UNAUTHORIZED);
        }
        Log::info('VerifyApiKey 5');
        return $next($request);
    }
}
