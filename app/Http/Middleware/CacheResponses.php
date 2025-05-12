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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $ttl = null)
    {
        $start  = microtime(true);
        $config = config('api_cache');

        // Bypass si no aplican métodos o rutas
        if (! $this->shouldCacheRequest($request, $config)) {
            $response = $next($request);
            return $this->finishResponse($response, 'BYPASS', $start);
        }

        // Generar clave de cache
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Cache key error: {$e->getMessage()}");
            $response = $next($request);
            return $this->finishResponse($response, 'BYPASS', $start);
        }

        // Intentar HIT
        try {
            if ($cached = Cache::get($key)) {
                $response = $this->buildCachedResponse($cached);
                // Añadir clave usada para depuración
                $response->headers->set('X-Cache-Key', $key);
                return $this->finishResponse($response, 'HIT', $start);
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        // MISS: procesar y luego guardar
        $response = $next($request);
        $origin   = strtolower($request->header('origin-component', ''));

        if (in_array($origin, ['hoster', 'huesped'])) {
            try {
                Cache::put($key, [
                    'timestamp' => now()->toDateTimeString(),
                    'route'     => $request->method() . ' ' . $request->path(),
                    'params'    => $this->normalize(
                        $request->isMethod('GET') ? $request->query() : $request->all()
                    ),
                    'status'    => $response->getStatusCode(),
                    'headers'   => $response->headers->all(),
                    'body'      => $response->getContent(),
                    'origin'    => $origin,
                ], $ttl ?? $config['default_ttl']);
            } catch (\Throwable $e) {
                Log::error("Cache save error: {$e->getMessage()}");
            }
        }

        return $this->finishResponse($response, 'MISS', $start);
    }

    /**
     * Añade headers de cache y tiempo.
     */
    protected function finishResponse(Response $response, string $status, float $start): Response
    {
        $elapsed = round((microtime(true) - $start) * 1000);
        return $response
            ->header('X-Cache', $status)
            ->header('X-Response-Time', "{$elapsed}ms")
            //->header('Cache-Control', 'private, max-age=3600')
            ->header('Cache-Control', 'no-store, must-revalidate')
            ->header('Vary', 'hash-user, hash-hotel, origin-component');
    }

    /**
     * Determina si la petición es cacheable.
     */
    protected function shouldCacheRequest(Request $request, array $config): bool
    {
        $method = strtoupper($request->getMethod());
        if (! in_array($method, ['GET', 'POST'])) {
            return false;
        }
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }
        // Requiere headers para caché: hash-user, hash-hotel y origin-component
        if (! $request->hasHeader('hash-user')
            || ! $request->hasHeader('hash-hotel')
            || ! $request->hasHeader('origin-component')) {
            return false;
        }
        if ($method === 'POST') {
            foreach ($config['cacheable_post_routes'] as $pattern) {
                if ($request->is($pattern)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Construye la clave de cache usando headers de usuario, hotel y origin.
     */
    protected function generateCacheKey(Request $request, array $config): string
    {
        $userHash  = $request->header('hash-user');
        $hotelHash = $request->header('hash-hotel');
        $origin    = strtolower($request->header('origin-component', ''));

        if (empty($userHash) || empty($hotelHash) || empty($origin)) {
            throw new \RuntimeException('Missing identifiers for cache key');
        }

        $path   = $request->path();
        $params = $this->normalize(
            $request->isMethod('GET') ? $request->query() : $request->all()
        );

        return sprintf(
            '%suser:%s:hotel:%s:origin:%s:path:%s:%s',
            $config['key_prefix'], $userHash, $hotelHash,
            $origin, $path,
            sha1($path . '|' . json_encode($params))
        );
    }

    /**
     * Ordena parámetros para hashing.
     */
    protected function normalize(array $params): array
    {
        ksort($params);
        return $params;
    }

    /**
     * Reconstruye la respuesta cacheada.
     */
    protected function buildCachedResponse(array $c): Response
    {
        $response = response($c['body'], $c['status']);
        foreach ($c['headers'] as $name => $vals) {
            foreach ((array) $vals as $v) {
                $response->header($name, $v);
            }
        }
        return $response;
    }
}
