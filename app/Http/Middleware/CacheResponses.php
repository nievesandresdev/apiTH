<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CacheResponses
{
    public function handle(Request $request, Closure $next, $ttl = null)
    {
        // 1. Solo GET
        if (!$request->isMethod('GET')) {
            return $this->addCacheHeader($next($request), 'BYPASS-METHOD');
        }

        // 2. Configuración
        $config = config('api_cache', [
            'default_ttl' => 300,
            'excluded_routes' => [],
            'route_specific_ttl' => [],
            'key_prefix' => 'api:response:'
        ]);

        // 3. Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return $this->addCacheHeader($next($request), 'BYPASS-ROUTE');
            }
        }

        // 4. Forzar bypass
        if ($request->has('no-cache')) {
            return $this->addCacheHeader($next($request), 'BYPASS-FORCE');
        }

        // 5. Generar clave
        $key = $this->generateCacheKey($request, $config['key_prefix']);

        // 6. Intentar obtener respuesta cacheada (lectura directa)
        try {
            if ($cached = Cache::get($key)) {
                return $this->addCacheHeader($cached, 'HIT');
            }
        } catch (\Throwable $e) {
            Log::warning("Redis read failed: ".$e->getMessage());
        }

        // 7. Ejecutar y cachear si es exitoso
        $response = $next($request);

        if ($response->isSuccessful()) {
            try {
                Cache::put($key, $response, $this->getFinalTtl($request, $ttl, $config));
                return $this->addCacheHeader($response, 'MISS');
            } catch (\Throwable $e) {
                Log::warning("Redis write failed: ".$e->getMessage());
            }
        }

        return $this->addCacheHeader($response, 'BYPASS-STATUS');
    }

    protected function generateCacheKey(Request $request, string $prefix): string
    {
        return sprintf('%s%s:%s:%s',
            $prefix,
            $request->user()?->id ?: 'guest',
            sha1($request->path()),
            sha1(http_build_query($request->query()))
        );
    }

    protected function getFinalTtl(Request $request, $ttl, array $config): int
    {
        if ($ttl !== null) {
            return $ttl;
        }

        foreach ($config['route_specific_ttl'] as $route => $routeTtl) {
            if ($request->is($route)) {
                return $routeTtl;
            }
        }

        return $config['default_ttl'];
    }

    protected function addCacheHeader(Response $response, string $value): Response
    {
        $response->headers->set('X-Cache', $value);
        return $response;
    }
}