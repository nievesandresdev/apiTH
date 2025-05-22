<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

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
        if (! config('api_cache.enabled')) {
            return $next($request);
        }
        
        $start  = microtime(true);
        $config = config('api_cache');

        // revisar si aplica para cache CACHE-NOT-ALLOWED
        if (! $this->shouldCacheRequest($request, $config)) {
            $response = $next($request);
            return $this->finishResponse($response, 'CACHE-NOT-ALLOWED', $start);
        }

        // Generar clave de cache
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Cache key error: {$e->getMessage()}");
            $response = $next($request);
            return $this->finishResponse($response, 'CACHE-NOT-ALLOWED-FOR-KEY', $start);
        }

        // traer desde el cache BROUGHT-FROM-CACHE
        try {
            if ($cached = Cache::get($key)) {
                    $response = $this->buildCachedResponse($cached);
                    $response->headers->set('X-Cache-Key', $key);
                    return $this->finishResponse($response, 'BROUGHT-FROM-CACHE', $start);
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        } 

        // CACHED: procesar y luego guardar
        $response = $next($request);
        $origin   = strtolower($request->header('origin-component'));

        if (in_array($origin, ['hoster', 'huesped'])) {

            $filteredHeaders = $this->filterResponseHeaders($response->headers->all());

            try {
                Cache::put($key, [
                    'timestamp' => now()->toDateTimeString(),
                    'route'     => $request->method() . ' ' . $request->path(),
                    'params'    => $this->normalize(
                        $request->isMethod('GET') ? $request->query() : $request->all()
                    ),
                    'status'    => $response->getStatusCode(),
                    'headers'   => $filteredHeaders,   // <-- aquí
                    'body'      => $response->getContent(),
                    'origin'    => $origin,
                ], $ttl ?? $config['default_ttl']);
            } catch (\Throwable $e) {
                Log::error("Cache save error: {$e->getMessage()}");
            }
        }

        return $this->finishResponse($response, 'CACHED', $start);
    }

    /**
     * Añade headers de cache y tiempo.
     */
    protected function finishResponse(Response $response, string $status, float $start): Response
    {
        $elapsed = round((microtime(true) - $start) * 1000);

        $ttl = property_exists($this, 'ttl')
        ? $this->ttl
        : config('api_cache.default_ttl');

        $response->header('X-Cache', $status)
                ->header('X-Response-Time', "{$elapsed}ms")
                ->header('Vary', 'hash-user, hash-hotel, origin-component, reset-cache');

            // 2) Cache-Control según el estado
            if ($status === 'BYPASS') {
                $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            } else {
                $response->header('Cache-Control', 'public, max-age=' . $ttl);
            }
        return $response;
                
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

        if ($request->query('mockup') === 'true') {
            return false;
        }
        
        $pathWithoutQuery = strtok($request->getRequestUri(), '?');

        $pathForCheck = ltrim(parse_url($pathWithoutQuery, PHP_URL_PATH), '/');

        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // Requiere headers para caché: hash-user, hash-hotel y origin-component
        if (! $request->hasHeader('hash-user')
            || ! $request->hasHeader('hash-hotel')
            || ! $request->hasHeader('origin-component')
            || ! $request->hasHeader('reset-cache')) {
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
        $resetCache = $request->header('reset-cache');
        $origin    = strtolower($request->header('origin-component'));

        if (empty($userHash) || empty($hotelHash) || empty($origin) || empty($resetCache)) {
            throw new \RuntimeException('Missing identifiers for cache key');
        }

        $path   = $request->path();
        $params = $this->normalize(
            $request->isMethod('GET') ? $request->query() : $request->all()
        );
        
        return sprintf(
            '%suser:%s:hotel:%s:origin:%s:reset:%s:path:%s:%s',
            $config['key_prefix'], $userHash, $hotelHash,
            $origin, $resetCache, $path,
            sha1($path . '|' . json_encode($params))
        );
    }

    /**
     * Ordena parámetros para hashing.
     */
    protected function normalize(array $params): array
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $params[$key] = $this->normalize($value);
            }
        }
        ksort($params);
        return $params;
    }

    /**
     * Reconstruye la respuesta cacheada.
     */
    protected function buildCachedResponse(array $c): Response
    {
        $response = response($c['body'], $c['status']);

        // 2) APLICAMOS sólo los headers permitidos al reconstruir la respuesta
        foreach ($this->filterResponseHeaders($c['headers']) as $name => $vals) {
            foreach ((array) $vals as $v) {
                $response->header($name, $v);
            }
        }

        return $response;
    }

    protected function filterResponseHeaders(array $headers): array
    {
        $exclude = [
            'subdomainhotel',
            'reset-cache',
            'chainsubdomain',
            'hash-hotel',
            'hash-user',
            'origin-component',
            ':path',
        ];

        return array_filter(
            $headers,
            function (string $name) use ($exclude) {
                return ! in_array(strtolower($name), $exclude, true);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
