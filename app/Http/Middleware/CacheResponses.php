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
        if (! $this->isCacheableRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS-ROUTE');
        }

        // 2. Generar clave
        $key = $this->generateCacheKey($request, $config['key_prefix']);

        // 3. Intento de lectura de cache
        try {
            if ($cached = Cache::get($key)) {
                // Reconstruir la Response exactamente igual
                return response($cached['content'], $cached['status'])
                    ->withHeaders($cached['headers'])
                    ->header('X-Cache', 'HIT');
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        // 4. Procesar solicitud
        $response = $next($request);

        // 5. Almacenar en cache si es exitoso
        if ($response->isSuccessful()) {
            $this->storeResponse($request, $response, $key, $ttl, $config);
            // devolvemos fresh (ya con header MD en storeResponse no modifica el objeto)
            return $this->addCacheHeader($response, 'MISS');
        }

        return $this->addCacheHeader($response, 'BYPASS-STATUS');
    }

    protected function isCacheableRequest(Request $request, array $config): bool
    {
        $method = strtoupper($request->getMethod());

        // Solo GET o POST permitidos
        if (! in_array($method, ['GET', 'POST'])) {
            return false;
        }

        // Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // POSTs permitidos explícitamente
        if ($method === 'POST') {
            return collect($config['cacheable_post_routes'])
                ->contains(fn($route) => $request->is($route));
        }

        // GETs permitidos por defecto
        return true;
    }

    protected function generateCacheKey(Request $request, string $prefix): string
    {
        $userId = $request->user()?->id ?: 'guest';
        $path   = $request->path();
        $params = $request->isMethod('GET')
            ? http_build_query($request->query())
            : json_encode($request->except($request->isMethod('POST')
                ? $request->merge($request->all())->except($request->isMethod('POST') ? config('api_cache.sensitive_params', []) : [])
                : []));

        return "{$prefix}{$userId}:" . sha1("{$path}|{$params}");
    }

    protected function storeResponse(Request $request, Response $response, string $key, $ttl, array $config): void
    {
        try {
            $finalTtl = $ttl
                ?? $this->getRouteTtl($request, $config)
                ?? $config['default_ttl'];

            $payload = [
                'content' => $response->getContent(),
                'status'  => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ];

            Cache::put($key, $payload, $finalTtl);
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
        // Si ya venía un Cache-Control de tu controlador, lo preservamos:
        if (! $response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'max-age=3600, public');
        }
        return $response;
    }
}
