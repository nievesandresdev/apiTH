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
        // Cargar configuración
        $config = config('api_cache');

        // Verificar si debe cachear
        if (! $this->shouldCacheRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS');
        }

        // Generar clave con fallback
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Error generando cache key: {$e->getMessage()}");
            return $this->addCacheHeader($next($request), 'BYPASS');
        }

        // Intentar recuperar del cache
        try {
            if ($cached = Cache::get($key)) {
                return response($cached['body'], $cached['status'])
                    ->withHeaders($cached['headers'])
                    ->header('X-Cache', 'HIT');
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
                // Leer identificadores de usuario y hotel
                $userHash  = $request->header('has-user');
                $hotelHash = $request->header('has-hotel');

                if ($userHash && $hotelHash) {
                    // Ruta y método para identificar endpoint
                    $method = $request->method();
                    $path   = $request->path();
                    $params = $request->isMethod('GET')
                        ? $request->query()
                        : $request->all();

                    // Guardar payload en Redis
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
        return $this->addCacheHeader($response, 'MISS');
    }

    /**
     * Determina si la petición debe ser cacheada.
     */
    protected function shouldCacheRequest(Request $request, array $config): bool
    {
        $method = strtoupper($request->getMethod());

        // Solo GET y POST permitidos
        if (! in_array($method, ['GET', 'POST'])) {
            return false;
        }

        // Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // Para POST, solo patrones permitidos
        if ($method === 'POST') {
            foreach ($config['cacheable_post_routes'] as $pattern) {
                if ($request->is($pattern)) {
                    return true;
                }
            }
            return false;
        }

        // Para GET siempre
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

        // Asegurar orden consistente de parámetros
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
