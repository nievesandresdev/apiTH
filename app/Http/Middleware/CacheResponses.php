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

        // 1) ¿Cacheable? solo GET o POST explicitados, y rutas no excluidas
        if (! $this->isCacheableRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS-ROUTE');
        }

        // 2) Generar clave: prefix + userId + hotel + path|params
        $key = $this->generateCacheKey($request, $config['key_prefix']);

        // 3) Intentar leer desde Redis
        try {
            if ($cached = Cache::get($key)) {
                return response($cached['content'], $cached['status'])
                    ->withHeaders($cached['headers'])
                    ->header('X-Cache', 'HIT');
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        // 4) Ejecutar petición real
        $response = $next($request);

        // 5) Si es 2xx, almacenar en Redis
        if ($response->isSuccessful()) {
            $this->storeResponse($request, $response, $key, $ttl, $config);
            return $this->addCacheHeader($response, 'MISS');
        }

        // 6) Bypass si no es 2xx
        return $this->addCacheHeader($response, 'BYPASS-STATUS');
    }

    protected function isCacheableRequest(Request $request, array $config): bool
    {
        $method = strtoupper($request->getMethod());

        // Solo GET o POST listados en config
        if (! in_array($method, ['GET', 'POST'])) {
            return false;
        }

        // Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // Si es POST, debe estar en la lista
        if ($method === 'POST') {
            return collect($config['cacheable_post_routes'])
                ->contains(fn($route) => $request->is($route));
        }

        // GET siempre es cacheable si llegó hasta aquí
        return true;
    }

    protected function generateCacheKey(Request $request, string $prefix): string
    {
        $config    = config('api_cache');
        $userId    = $request->user()?->id ?: 'guest';
        // Tomamos también el hotel/cadena desde el header
        $hotel     = $request->header('subdomainhotel', 'guest_hotel');
        $path      = $request->path();
        $sensitive = $config['sensitive_params'] ?? [];

        if ($request->isMethod('GET')) {
            $paramsString = http_build_query($request->query());
        } else {
            $body = $request->except($sensitive);
            $paramsString = json_encode($body);
        }

        // clave: prefix + user + hotel + hash(path|params)
        $composite = implode('|', [
            $path,
            $paramsString,
        ]);

        return "{$prefix}{$userId}:{$hotel}:" . sha1($composite);
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
        foreach ($config['route_specific_ttl'] as $route => $routeTtl) {
            if ($request->is($route)) {
                return $routeTtl;
            }
        }
        return null;
    }

    protected function addCacheHeader(Response $response, string $value): Response
    {
        $response->headers->set('X-Cache', $value);
        if (! $response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'max-age=3600, public');
        }
        return $response;
    }
}
