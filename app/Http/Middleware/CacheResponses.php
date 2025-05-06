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
        // 1) Métodos cacheables: GET siempre, POST sólo si la ruta está en config
        $method = strtoupper($request->getMethod());
        $config = config('api_cache');

        $isCacheableMethod = $method === 'GET';
        $isCacheablePost   = $method === 'POST' &&
                             collect($config['cacheable_post_routes'])
                               ->contains(fn($route) => $request->is($route));

        if (! $isCacheableMethod && ! $isCacheablePost) {
            return $this->addCacheHeader($next($request), 'BYPASS-METHOD');
        }

        // 2) Excluded routes
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return $this->addCacheHeader($next($request), 'BYPASS-ROUTE');
            }
        }

        // 3) Force bypass
        if ($request->has('no-cache')) {
            return $this->addCacheHeader($next($request), 'BYPASS-FORCE');
        }

        // 4) Generar clave
        $key = $this->generateCacheKey($request, $config['key_prefix']);

        // 5) Lectura temprana
        try {
            if ($cached = Cache::get($key)) {
                return $this->addCacheHeader($cached, 'HIT');
            }
        } catch (\Throwable $e) {
            Log::warning("Redis read failed: ".$e->getMessage());
        }

        // 6) Ejecutar petición real
        $response = $next($request);

        // 7) Sólo cachear si es 2xx
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
        $userId = $request->user()?->id ?: 'guest';
        $path   = $request->path();
        // Incluimos tanto query como body para POST
        $params = $request->isMethod('GET')
            ? http_build_query($request->query())
            : sha1(json_encode($request->all()));

        return sprintf('%s%s:%s:%s',
            $prefix,
            $userId,
            sha1($path),
            sha1($params)
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
