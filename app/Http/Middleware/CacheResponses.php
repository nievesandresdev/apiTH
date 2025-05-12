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
        // Iniciar cronómetro
        $start = microtime(true);

        // Cargar configuración
        $config = config('api_cache');

        // Verificar si debe cachear
        if (! $this->shouldCacheRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS', $start);
        }

        // Generar clave con fallback
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Error generando cache key: {$e->getMessage()}");
            return $this->addCacheHeader($next($request), 'BYPASS', $start);
        }

        // Intentar recuperar del cache
        try {
            if ($cached = Cache::get($key)) {
                $response = $this->buildCachedResponse($cached);
                return $this->prepareResponse($response, $start, 'HIT');
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        // Procesar petición real
        $response = $next($request);

        // Obtener origen del request
        $origin = $request->header('origin-component');

        // Si viene de Hoster o Huesped, guardar en cache
        if (in_array(strtolower($origin), ['hoster', 'huesped'])) {
            try {
                $userHash  = $request->header('has-user');
                $hotelHash = $request->header('has-hotel');

                if ($userHash && $hotelHash) {
                    $method = $request->method();
                    $path   = $request->path();
                    $params = $request->isMethod('GET') ? $request->query() : $request->all();

                    Cache::put($key, [
                        'timestamp' => now()->toDateTimeString(),
                        'route'     => "$method $path",
                        'params'    => $params,
                        'status'    => $response->getStatusCode(),
                        'headers'   => $response->headers->all(),
                        'body'      => $response->getContent(),
                        'origin'    => $origin,
                    ], $ttl ?? $config['default_ttl']);
                }
            } catch (\Throwable $e) {
                Log::error("Cache save error ({$origin}): {$e->getMessage()}");
            }
        }

        // Devolver respuesta original con código de cache
        return $this->addCacheHeader($response, 'MISS', $start);
    }

    /**
     * Determina si la petición debe ser cacheada.
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
        $requiredHeaders = ['has-user', 'has-hotel', 'origin-component'];
        foreach ($requiredHeaders as $header) {
            if (!$request->hasHeader($header)) {
                return false;
            }
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
     * Genera una clave única de cache.
     */
    protected function generateCacheKey(Request $request, array $config): string
    {
        $userHash        = $request->header('has-user');
        $hotelHash       = $request->header('has-hotel');
        $originComponent = strtolower($request->header('origin-component'));

        if (empty($userHash) || empty($hotelHash) || empty($origin)) {
            throw new \RuntimeException('Missing required headers for cache key');
        }

        $path            = $request->path();
        $params          = $this->normalizeParameters($request);

        return sprintf(
            '%suser:%s:hotel:%s:origin:%s:path:%s:%s',
            $config['key_prefix'], // prefijo desde config/api_cache
            $userHash,
            $hotelHash,
            $originComponent,
            $path,
            sha1($path . '|' . json_encode($params))
        );
    }

    /**
     * Normaliza los parámetros de la petición.
     */
    protected function normalizeParameters(Request $request): array
    {
        $params = $request->isMethod('GET') 
            ? $request->query()
            : $request->except(config('api_cache.sensitive_params', []));

        ksort($params);
        return $params;
    }

    protected function buildCachedResponse(array $cached): Response
    {
        $response = response($cached['body'], $cached['status']);

        foreach ($cached['headers'] as $name => $values) {
            if ($name !== 'cache-control') {
                $response->header($name, $values[0]);
            }
        }

        return $response;
    }

    /**
     * Agrega cabecera X-Cache
     */
    protected function addCacheHeader(Response $response, string $status, float $startTime): Response
    {
        $elapsed = round((microtime(true) - $startTime) * 1000);
        return $response
            ->header('X-Cache', $cacheStatus)
            ->header('X-Response-Time', "{$elapsed}ms")
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
