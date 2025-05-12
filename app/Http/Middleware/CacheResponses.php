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
        // Iniciar cronómetro
        $start = microtime(true);

        // Configuración
        $config = config('api_cache');

        // Si no debe cachear, bypass
        if (! $this->shouldCacheRequest($request, $config)) {
            $response = $next($request);
            return $this->finishResponse($response, 'BYPASS', $start);
        }

        // Generar clave
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Error generando cache key: {$e->getMessage()}");
            $response = $next($request);
            return $this->finishResponse($response, 'BYPASS', $start);
        }

        // Intentar HIT
        try {
            if ($cached = Cache::get($key)) {
                $response = $this->buildCachedResponse($cached);
                return $this->finishResponse($response, 'HIT', $start);
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        // MISS: solicitud real
        $response = $next($request);

        // Guardar en cache si aplica
        $origin = strtolower($request->header('origin-component', ''));
        if (in_array($origin, ['hoster', 'huesped'])) {
            try {
                $userHash  = $request->header('has-user');
                $hotelHash = $request->header('has-hotel');
                if ($userHash && $hotelHash) {
                    $method = $request->method();
                    $path   = $request->path();
                    $params = $request->isMethod('GET') ? $request->query() : $request->all();

                    Cache::put($key, [
                        'timestamp'=> now()->toDateTimeString(),
                        'route'    => "$method $path",
                        'params'   => $this->normalize($params),
                        'status'   => $response->getStatusCode(),
                        'headers'  => $response->headers->all(),
                        'body'     => $response->getContent(),
                        'origin'   => $origin,
                    ], $ttl ?? $config['default_ttl']);
                }
            } catch (\Throwable $e) {
                Log::error("Cache save error ({$origin}): {$e->getMessage()}");
            }
        }

        // Devolver MISS
        return $this->finishResponse($response, 'MISS', $start);
    }

    /**
     * Finaliza respuesta: añade headers
     */
    protected function finishResponse(Response $response, string $status, float $start): Response
    {
        $elapsed = round((microtime(true) - $start) * 1000);
        return $response
            ->header('X-Cache', $status)
            ->header('X-Response-Time', "{$elapsed}ms")
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Determina si la petición debe ser cacheada.
     */
    protected function shouldCacheRequest(Request $request, array $config): bool
    {
        $method = strtoupper($request->getMethod());
        if (! in_array($method, ['GET', 'POST'])) return false;
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) return false;
        }
        $required = ['has-user','has-hotel','origin-component'];
        foreach ($required as $h) {
            if (! $request->hasHeader($h)) return false;
        }
        if ($method === 'POST') {
            foreach ($config['cacheable_post_routes'] as $p) {
                if ($request->is($p)) return true;
            }
            return false;
        }
        return true;
    }

    /**
     * Genera clave única con hash de params normalizados.
     */
    protected function generateCacheKey(Request $request, array $config): string
    {
        $user  = $request->header('has-user');
        $hotel = $request->header('has-hotel');
        $orig  = strtolower($request->header('origin-component'));
        if (empty($user) || empty($hotel) || empty($orig)) {
            throw new \RuntimeException('Missing headers for cache key');
        }
        $path   = $request->path();
        $params = $this->normalize($request->isMethod('GET') ? $request->query() : $request->all());
        return sprintf(
            '%suser:%s:hotel:%s:origin:%s:path:%s:%s',
            $config['key_prefix'], $user, $hotel, $orig,
            $path, sha1($path.'|'.json_encode($params))
        );
    }

    /**
     * Normaliza parámetros ordenando claves.
     */
    protected function normalize(array $params): array
    {
        ksort($params);
        return $params;
    }

    /**
     * Reconstruye la Response a partir del payload.
     */
    protected function buildCachedResponse(array $c): Response
    {
        $response = response($c['body'], $c['status']);
        foreach ($c['headers'] as $name => $vals) {
            foreach ((array)$vals as $v) {
                $response->header($name, $v);
            }
        }
        return $response;
    }
}
