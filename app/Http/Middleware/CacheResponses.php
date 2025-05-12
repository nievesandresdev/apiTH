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

        // Identificar si la llamada viene del microservicio Hoster
        $origin = $request->header('origin-component');

        // Procesar petición
        $response = $next($request);

        // Si viene de Hoster, guardar en cache (solo almacenar)
        if (strtolower($origin) === 'hoster') {
            try {
                // Leer identificadores de usuario y hotel
                $userHash      = $request->header('has-user');
                $hotelHash     = $request->header('has-hotel');
                $originHeader  = $origin;

                if ($userHash && $hotelHash) {
                    // Ruta completa y método para identificar endpoint
                    $path   = $request->path();
                    $method = $request->method();
                    $params = $request->isMethod('GET')
                        ? $request->query()
                        : $request->all();

                    // Guardar en Redis
                    Cache::put($key, [
                        'timestamp' => now()->toDateTimeString(),
                        'route'     => $method . ' ' . $path,
                        'params'    => $params,
                        'status'    => $response->getStatusCode(),
                        'headers'   => $response->headers->all(),
                        'body'      => $response->getContent(),
                        'origin'    => $originHeader,
                    ], $ttl ?? $config['default_ttl']);
                }
            } catch (\Throwable $e) {
                Log::error("Cache save error (hoster): {$e->getMessage()}");
            }
        }

        // Devolver respuesta original con header
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
        // Leer headers
        $userHash        = $request->header('has-user');
        $hotelHash       = $request->header('has-hotel');
        $originComponent = $request->header('origin-component');

        // Ruta y parámetros para hash
        $path   = $request->path();
        $params = $request->isMethod('GET')
            ? $request->query()
            : $request->all();

        // Incluir origin en la clave
        return sprintf(
            '%suser:%s:hotel:%s:origin:%s:%s',
            $config['key_prefix'],   // prefijo desde config/api_cache
            $userHash,
            $hotelHash,
            $originComponent,
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
