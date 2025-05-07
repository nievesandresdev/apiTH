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

        if (!$this->shouldCacheRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS');
        }

        $key = $this->generateCacheKey($request, $config);
        
        try {
            if ($response = $this->getFromCache($key)) {
                return $response;
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: ".$e->getMessage());
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            $this->storeInCache($key, $response, $this->getFinalTtl($request, $ttl, $config));
        }

        return $this->addCacheHeader($response, $response->isSuccessful() ? 'MISS' : 'BYPASS');
    }

    protected function shouldCacheRequest(Request $request, array $config): bool
    {
        // Solo métodos permitidos
        if (!in_array($request->method(), ['GET', 'POST'])) {
            return false;
        }

        // Rutas excluidas
        foreach ($config['excluded_routes'] as $route) {
            if ($request->is($route)) {
                return false;
            }
        }

        // POSTs deben estar en la lista blanca
        if ($request->isMethod('POST') && !in_array($request->path(), $config['cacheable_post_routes'])) {
            return false;
        }

        return true;
    }

    protected function generateCacheKey(Request $request, array $config): string
    {
        // 1. Identificador de usuario (obligatorio)
        $userId = $this->getUserIdFromRequest($request);
        
        // 2. Identificador de hotel (obligatorio)
        $hotelId = $this->getHotelIdFromRequest($request);
        
        // 3. Ruta y parámetros normalizados
        $path = $request->path();
        $params = $this->normalizeParameters($request);
        
        // Estructura clara: [prefijo][user][hotel][hash]
        return sprintf('%suser:%d:hotel:%s:%s',
            $config['key_prefix'],
            $userId,
            $hotelId,
            sha1($path.'|'.json_encode($params))
        );
    }

    protected function normalizeParameters(Request $request): array
    {
        $params = $request->isMethod('GET') 
            ? $request->query()
            : $request->except(config('api_cache.sensitive_params', []));

        // Normalización especial para hoteles
        if (isset($params['hotel'])) {
            $params['hotel'] = array_intersect_key($params['hotel'], [
                'id' => true, 
                'zone' => true
            ]);
            ksort($params['hotel']);
        }
    
        ksort($params);
        return $params;
    }

    protected function getUserIdFromRequest(Request $request): int
    {   
        if (!$request->bearerToken()) {
            throw new \RuntimeException('Se requiere autenticación para cachear');
        }
    
        try {
            $payload = json_decode(base64_decode(explode('.', $request->bearerToken())[1]));
            return $payload->sub ?? throw new \RuntimeException('Token no contiene sub');
        } catch (\Exception $e) {
            throw new \RuntimeException('Error leyendo token: '.$e->getMessage());
        }
    }

    protected function getHotelIdFromRequest(Request $request): string
    {
        $hotelId = $request->header('subdomainhotel');
        if (empty($hotelId) || $hotelId === 'no-hotel') {
            throw new \RuntimeException('Header subdomainhotel es requerido');
        }
        return $hotelId;
    }

    protected function normalizeRequestParameters(Request $request): array
    {
        $sensitive = config('api_cache.sensitive_params', []);
        $params = $request->isMethod('GET') 
            ? $request->query()
            : $request->except($sensitive);

        // Normalización especial para hoteles
        if (isset($params['hotel'])) {
            ksort($params['hotel']);
            
            // Conservamos solo los campos relevantes
            $params['hotel'] = array_intersect_key($params['hotel'], [
                'id' => true,
                'zone' => true,
                'latitude' => true,
                'longitude' => true
            ]);
        }

        // Ordenamos los parámetros principales
        ksort($params);

        return $params;
    }

    protected function getFromCache(string $key): ?Response
    {
        if ($cached = Cache::get($key)) {
            return response($cached['content'], $cached['status'])
                ->withHeaders($cached['headers'])
                ->header('X-Cache', 'HIT')
                ->header('X-Cache-Key', $key);
        }
        return null;
    }

    protected function storeInCache(string $key, Response $response, int $ttl): void
    {
        try {
            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all()
            ], $ttl);
        } catch (\Throwable $e) {
            Log::error("Cache write error: ".$e->getMessage());
        }
    }

    protected function getFinalTtl(Request $request, $ttl, array $config): int
    {
        if ($ttl !== null) {
            return $ttl;
        }
        
        foreach ($config['route_specific_ttl'] as $route => $routeTtl) {
            if ($request->is($route)) {
                return $routeTtl;
            }
        }
        
        return $config['default_ttl'];
    }

    protected function addCacheHeader(Response $response, string $status): Response
    {
        return $response->header('X-Cache', $status)
            ->header('Cache-Control', 'private, max-age=3600');
    }
}