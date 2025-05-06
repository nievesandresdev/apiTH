<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        // 1. Verificar si es método GET
        if (!$request->isMethod('get')) {
            return $next($request)->header('X-Cache', 'BYPASS-METHOD');
        }

        // 2. Obtener configuración
        $config = config('api_cache', [
            'default_ttl' => 300,
            'excluded_routes' => [],
            'route_specific_ttl' => []
        ]);

        // 3. Verificar rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return $next($request)->header('X-Cache', 'BYPASS-ROUTE');
            }
        }

        // 4. Bypass cache si se solicita explícitamente
        if ($request->has('no-cache')) {
            return $next($request)->header('X-Cache', 'BYPASS-FORCE');
        }

        // 5. Determinar TTL (prioridad: parámetro > ruta específica > default)
        $finalTtl = $ttl ?? $this->getRouteSpecificTtl($request, $config) ?? $config['default_ttl'];

        // 6. Generar clave de cache
        $key = $this->generateCacheKey($request);

        // 7. Manejar la respuesta
        $response = $next($request);

        // 8. Cachear solo si es exitosa (200-299) y GET
        if ($response->isSuccessful()) {
            Cache::put($key, $response, $finalTtl);
            return $response->header('X-Cache', 'MISS');
        }

        // 9. Si no es exitosa, devolver sin cachear
        return $response->header('X-Cache', 'BYPASS-STATUS');
    }

    /**
     * Genera una clave de cache única para la solicitud
     */
    protected function generateCacheKey(Request $request): string
    {
        $userId = $request->user()?->id ?: 'guest';
        $path = $request->path();
        $query = http_build_query($request->query());

        return sprintf('api:response:%s:%s:%s',
            $userId,
            sha1($path),
            sha1($query)
        );
    }

    /**
     * Obtiene el TTL específico para la ruta si está configurado
     */
    protected function getRouteSpecificTtl(Request $request, array $config): ?int
    {
        foreach ($config['route_specific_ttl'] ?? [] as $route => $ttl) {
            if ($request->is($route)) {
                return $ttl;
            }
        }

        return null;
    }
}