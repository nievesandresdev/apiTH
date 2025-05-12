<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CacheResponses
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|null  $ttl
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $ttl = null)
    {
        // Identificar si la llamada viene del microservicio Hoster
        $origin = $request->header('origin-component');

        // Procesar petición
        $response = $next($request);

        if (strtolower($origin) === 'hoster') {
            try {
                // Leer identificadores de usuario y hotel
                $userHash  = $request->header('has-user');
                $hotelHash = $request->header('has-hotel');

                if ($userHash && $hotelHash) {
                    // Generar clave única: prefix + user + hotel + ruta y params
                    $prefix = config('api_cache.key_prefix', 'api:response:');
                    $path   = $request->path();
                    $params = $request->isMethod('GET')
                        ? $request->query()
                        : $request->all();

                    $key = sprintf(
                        '%suser:%s:hotel:%s:%s',
                        $prefix,
                        $userHash,
                        $hotelHash,
                        sha1($path . '|' . json_encode($params))
                    );

                    // Guardar en Redis (solo almacenar, no recuperar)
                    Cache::put($key, [
                        'timestamp' => now()->toDateTimeString(),
                        'status'    => $response->getStatusCode(),
                        'headers'   => $response->headers->all(),
                        'body'      => $response->getContent(),
                    ], $ttl ?? config('api_cache.default_ttl', 300));
                }
            } catch (\Throwable $e) {
                Log::error("Cache save error (hoster): {$e->getMessage()}");
            }
        }

        return $response;
    }
}
