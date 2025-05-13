<?php

return [
    'default_ttl' => 86400, // 1 hora por defecto
    
    'excluded_routes' => [
        'api/auth/*',
        //'api/user/profile',
        //'api/formularios/*'
    ],
    
    'route_specific_ttl' => [
        'api/place/*' => 86400, // 1 día para endpoints de lugares
    ],
    
    'key_prefix' => config('app.production') === 'true' ? 'hotel_prod_main:' : 'hotel_prod_test:',
    
    'cacheable_post_routes' => [
        'api/place/getPointers',
        'api/place/getAll',
        'api/place/getCategoriesByType'
    ],
    
    'required_headers' => [
        'subdomainhotel',
        'authorization'
    ],
    
    'sensitive_params' => [
        'password',
        'token',
        '_token',
        'credit_card'
    ]
];