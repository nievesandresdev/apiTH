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
        $config = config('api_cache');
        
        // 1. Verificar si la ruta es cacheable
        if (!$this->isCacheableRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS-ROUTE');
        }

        // 2. Generar clave considerando método
        $key = $this->generateCacheKey($request, $config['key_prefix']);

        // 3. Intento de lectura de cache
        try {
            if ($response = Cache::get($key)) {
                return $this->addCacheHeader($response, 'HIT');
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        // 4. Procesar solicitud
        $response = $next($request);

        // 5. Almacenar en cache si es exitoso
        if ($response->isSuccessful()) {
            $this->storeResponse($request, $response, $key, $ttl, $config);
        }

        return $this->addCacheHeader($response, $response->isSuccessful() ? 'MISS' : 'BYPASS-STATUS');
    }

    protected function isCacheableRequest(Request $request, array $config): bool
    {
        // Métodos permitidos
        if (!in_array($request->getMethod(), ['GET', 'POST'])) {
            return false;
        }

        // Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // POSTs permitidos explícitamente
        if ($request->isMethod('POST')) {
            return collect($config['cacheable_post_routes'])
                ->contains(fn($route) => $request->is($route));
        }

        // GETs permitidos por defecto
        return true;
    }

    protected function generateCacheKey(Request $request, string $prefix): string
    {
        $userId = $request->user()?->id ?: 'guest';
        $path = $request->path();
        $input = $request->isMethod('GET') 
            ? http_build_query($request->query())
            : json_encode($request->except(['password', 'token', '_token']));

        return "{$prefix}{$userId}:".sha1("{$path}|{$input}");
    }

    protected function storeResponse(Request $request, Response $response, string $key, $ttl, array $config): void
    {
        try {
            $finalTtl = $ttl ?? $this->getRouteTtl($request, $config) ?? $config['default_ttl'];
            
            Cache::put($key, $response, $finalTtl);
        } catch (\Throwable $e) {
            Log::error("Cache write error: {$e->getMessage()}");
        }
    }

    protected function getRouteTtl(Request $request, array $config): ?int
    {
        foreach ($config['route_specific_ttl'] as $route => $ttl) {
            if ($request->is($route)) {
                return $ttl;
            }
        }
        return null;
    }

    protected function addCacheHeader(Response $response, string $value): Response
    {
        $response->headers->set('X-Cache', $value);
        $response->headers->set('Cache-Control', 'max-age=3600, public');
        return $response;
    }
}