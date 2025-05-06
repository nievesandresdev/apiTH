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
        // 1. Sólo GET
        if (!$request->isMethod('get')) {
            return $next($request)->header('X-Cache', 'BYPASS-METHOD');
        }

        // 2. Configuración
        $config = config('api_cache', [
            'default_ttl'        => 300,
            'excluded_routes'    => [],
            'route_specific_ttl' => [],
            'key_prefix'         => 'api:response:'
        ]);

        // 3. Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return $next($request)->header('X-Cache', 'BYPASS-ROUTE');
            }
        }

        // 4. Forzar bypass
        if ($request->has('no-cache')) {
            return $next($request)->header('X-Cache', 'BYPASS-FORCE');
        }

        // 5. TTL final
        $finalTtl = $ttl
            ?? $this->getRouteSpecificTtl($request, $config)
            ?? $config['default_ttl'];

        // 6. Generar clave
        $key = $this->generateCacheKey($request, $config['key_prefix']);

        // ——— 7. Intentar lectura temprana ———
        try {
            if (Cache::has($key)) {
                $cached = Cache::get($key);
                return response($cached['content'], $cached['status'])
                    ->withHeaders($cached['headers'])
                    ->header('X-Cache', 'HIT');
            }
        } catch (\Throwable $e) {
            Log::warning("Redis read failed: ".$e->getMessage());
            // continúa al controlador sin romper la petición
        }

        // ——— 8. Ejecutar lógica real ———
        $response = $next($request);

        // 9. Cachear sólo si es 2xx
        if ($response->isSuccessful()) {
            try {
                $data = [
                    'content' => $response->getContent(),
                    'status'  => $response->getStatusCode(),
                    'headers' => $response->headers->all(),
                ];
                Cache::put($key, $data, $finalTtl);
            } catch (\Throwable $e) {
                Log::warning("Redis write failed: ".$e->getMessage());
            }
            return response($data['content'], $data['status'])
                ->withHeaders($data['headers'])
                ->header('X-Cache', 'MISS');
        }

        // 10. Bypass si no es 2xx
        return $response->header('X-Cache', 'BYPASS-STATUS');
    }

    protected function generateCacheKey(Request $request, string $prefix): string
    {
        $userId = $request->user()?->id ?: 'guest';
        $path   = $request->path();
        $query  = http_build_query($request->query());

        return sprintf('%s%s:%s:%s',
            $prefix,
            $userId,
            sha1($path),
            sha1($query)
        );
    }

    protected function getRouteSpecificTtl(Request $request, array $config): ?int
    {
        foreach ($config['route_specific_ttl'] as $route => $ttl) {
            if ($request->is($route)) {
                return $ttl;
            }
        }
        return null;
    }
}
