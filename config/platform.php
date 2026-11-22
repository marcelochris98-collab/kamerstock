<?php

return [
    'tenant_resolution_enabled' => env('TENANT_RESOLUTION_ENABLED', true),
    'tenancy_enabled' => env('TENANCY_ENABLED', true),
    'tenant_query_parameter' => 'tenant',
    'tenant_path_prefix' => 't',
    'tenant_database_directory' => env('TENANT_DATABASE_DIRECTORY', database_path('tenants')),
    'central_domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('PLATFORM_CENTRAL_DOMAINS', 'localhost,127.0.0.1'))
    ))),
    'central_routes' => [
        'register',
        'forgot-password',
        'reset-password',
    ],
];
