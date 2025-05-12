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
            $response = $next($request);
            // Medir tiempo interno
            $elapsed = round((microtime(true) - $start) * 1000);
            $response->headers->set('X-Response-Time', "{$elapsed}ms");
            return $this->addCacheHeader($response, 'BYPASS');
        }

        // Generar clave con fallback
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Error generando cache key: {$e->getMessage()}");
            $response = $next($request);
            $elapsed = round((microtime(true) - $start) * 1000);
            $response->headers->set('X-Response-Time', "{$elapsed}ms");
            return $this->addCacheHeader($response, 'BYPASS');
        }

        // Intentar recuperar del cache
        try {
            if ($cached = Cache::get($key)) {
                $response = response($cached['body'], $cached['status'])
                    ->withHeaders($cached['headers'])
                    ->header('X-Cache', 'HIT');
                // Medir tiempo interno
                $elapsed = round((microtime(true) - $start) * 1000);
                $response->headers->set('X-Response-Time', "{$elapsed}ms");
                return $response;
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

        // Medir tiempo interno antes de retornar
        $elapsed = round((microtime(true) - $start) * 1000);
        $response->headers->set('X-Response-Time', "{$elapsed}ms");

        // Devolver respuesta original con código de cache
        return $this->addCacheHeader($response, 'MISS');
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
        $path            = $request->path();
        $params          = $request->isMethod('GET') ? $request->query() : $request->all();

        if (is_array($params)) {
            ksort($params);
        }

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
     * Agrega cabecera X-Cache
     */
    protected function addCacheHeader(Response $response, string $status): Response
    {
        $response->headers->set('X-Cache', $status);
        return $response;
    }
}
