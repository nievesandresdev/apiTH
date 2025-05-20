<?php

return [
    'enabled' => env('CACHE_ENABLED', true),
    
    'default_ttl' => 1800, // 1 hora por defecto
    
    'excluded_routes' => [
        'api/auth/*',
        'api/guest/findByIdApi/*',
        //'api/user/profile',
        //'api/formularios/*'
    ],
    
    'route_specific_ttl' => [
        'api/place/*' => 1800, // 1 día para endpoints de lugares
    ],
    
    'key_prefix' => config('app.production') === 'true' ? 'hotel_prod_main:' : 'hotel_prod_test:',
    
    'cacheable_post_routes' => [
        'api/place/getPointers',
        'api/place/getAll',
        'api/place/getCategoriesByType'
    ],
    
    'required_headers' => [
        'subdomainhotel',
        'reset-cache',
        'authorization'
    ],
    
    'sensitive_params' => [
        'password',
        'token',
        '_token',
        'credit_card'
    ]
];