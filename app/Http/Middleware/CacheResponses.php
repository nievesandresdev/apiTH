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

        if (! $this->shouldCacheRequest($request, $config)) {
            return $this->addCacheHeader($next($request), 'BYPASS');
        }

        // Generación de clave con fallback en caso de error
        try {
            $key = $this->generateCacheKey($request, $config);
        } catch (\Throwable $e) {
            Log::warning("Error generando cache key: {$e->getMessage()}");
            return $this->addCacheHeader($next($request), 'BYPASS');
        }

        try {
            if ($cached = Cache::get($key)) {
                return response($cached['content'], $cached['status'])
                    ->withHeaders($cached['headers'])
                    ->header('X-Cache', 'HIT');
            }
        } catch (\Throwable $e) {
            Log::error("Cache read error: {$e->getMessage()}");
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            $this->storeInCache($key, $response, $this->getFinalTtl($request, $ttl, $config));
            return $this->addCacheHeader($response, 'MISS');
        }

        return $this->addCacheHeader($response, 'BYPASS');
    }

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

    protected function generateCacheKey(Request $request, array $config): string
    {
        $userId  = $this->getUserIdFromRequest($request);
        $hotelId = $this->getHotelIdFromRequest($request);
        $path    = $request->path();
        $params  = $this->normalizeParameters($request);

        $composite = $path . '|' . json_encode($params);

        return sprintf(
            '%suser:%s:hotel:%s:%s',
            $config['key_prefix'],
            $userId,
            $hotelId,
            sha1($composite)
        );
    }

    protected function normalizeParameters(Request $request): array
    {
        $sensitive = config('api_cache.sensitive_params', []);
        $params    = $request->isMethod('GET')
                   ? $request->query()
                   : $request->except($sensitive);

        if (isset($params['hotel']) && is_array($params['hotel'])) {
            $keep = ['id', 'zone', 'latitude', 'longitude'];
            $params['hotel'] = array_intersect_key(
                $params['hotel'], array_flip($keep)
            );
            ksort($params['hotel']);
        }

        ksort($params);
        return $params;
    }

    protected function getUserIdFromRequest(Request $request): string
    {
        if (! $token = $request->bearerToken()) {
            Log::warning('Token ausente, usando guest');
            return 'guest';
        }

        try {
            $payload = json_decode(
                base64_decode(explode('.', $token)[1]),
                true
            );
            return (string) ($payload['sub'] ?? 'guest');
        } catch (\Throwable $e) {
            Log::warning("Token inválido, usando guest: {$e->getMessage()}");
            return 'guest';
        }
    }

    protected function getHotelIdFromRequest(Request $request): string
    {
        $hotel = $request->header('subdomainhotel');
        if (empty($hotel)) {
            Log::warning('Header subdomainhotel ausente, usando no-hotel');
            return 'no-hotel';
        }
        return $hotel;
    }

    protected function storeInCache(string $key, Response $response, int $ttl): void
    {
        try {
            Cache::put($key, [
                'content' => $response->getContent(),
                'status'  => $response->getStatusCode(),
                'headers' => $response->headers->all(),
            ], $ttl);
        } catch (\Throwable $e) {
            Log::error("Cache write error: {$e->getMessage()}");
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
        $response->headers->set('X-Cache', $status);
        if (! $response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'private, max-age=3600');
        }
        return $response;
    }
}
