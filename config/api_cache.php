<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache TTL (en segundos)
    |--------------------------------------------------------------------------
    |
    | Tiempo por defecto que se almacenarán las respuestas en cache
    |
    */
    'default_ttl' => 300, // 5 minutos

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Rutas que NO deben ser cacheadas (patrones de ruta aceptados)
    |
    */
    'excluded_routes' => [
        'api/auth/*',
        'api/user/profile',
        'api/notifications*'
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Specific TTL
    |--------------------------------------------------------------------------
    |
    | TTL específico para ciertas rutas (sobrescribe el default)
    |
    */
    'route_specific_ttl' => [
        'api/products' => 86400,    // 1 día para productos
        'api/catalog' => 3600,      // 1 hora para catálogo
        'api/static/*' => 604800    // 1 semana para contenido estático
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | Prefijo para las claves de cache
    |
    */
    'key_prefix' => 'api:response:'
];