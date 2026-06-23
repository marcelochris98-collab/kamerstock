<?php

return [
    'default_trial_days' => 14,
    'grace_period_days' => 5,
    'read_only_after_days' => 6,
    'suspension_after_days' => 11,
    'default_currency' => 'FCFA',
    'tenancy_strategy' => 'database_per_tenant',

    // Database provisioning settings
    'enable_database_provisioning' => env('PLATFORM_ENABLE_DB_PROVISIONING', false),
    'tenant_database_prefix' => env('PLATFORM_TENANT_DB_PREFIX', 'kamerstock_tenant_'),
    'tenant_default_password_length' => 10,
    'tenant_owner_default_role' => 'Administrateur',

    // Multi-tenant configuration settings
    'tenancy_enabled' => env('PLATFORM_TENANCY_ENABLED', false),
    'tenant_resolution_enabled' => env('PLATFORM_TENANT_RESOLUTION_ENABLED', true),
    'central_domains' => [
        env('APP_DOMAIN', 'localhost'),
        '127.0.0.1',
        'localhost',
    ],
    'tenant_url_mode' => env('PLATFORM_TENANT_URL_MODE', 'query_or_subdomain'),
    'tenant_query_parameter' => 'tenant',
    'tenant_path_prefix' => 't',
    'tenant_connection_name' => 'tenant',
    'legacy_current_database_status' => 'legacy_current_db',
    'tenant_pending_statuses' => [
        'prepared',
        'pending',
    ],
    'tenant_ready_statuses' => [
        'legacy_current_db',
        'database_created',
        'migrated',
    ],

    'provisioning_modes' => [
        'prepared' => 'Préparé',
        'database_created' => 'Base de données créée',
        'migrated' => 'Migré',
        'legacy_current_db' => 'Base actuelle (Legacy)',
        'failed' => 'Échoué',
    ],

    'tenant_statuses' => [
        'trial' => 'Période d\'essai',
        'active' => 'Actif',
        'payment_due' => 'Paiement dû',
        'grace_period' => 'Période de grâce',
        'read_only' => 'Lecture seule',
        'suspended' => 'Suspendu',
        'archived' => 'Archivé',
    ],

    'subscription_statuses' => [
        'trial' => 'Essai',
        'active' => 'Actif',
        'expired' => 'Expiré',
        'cancelled' => 'Annulé',
        'suspended' => 'Suspendu',
    ],

    'payment_statuses' => [
        'pending' => 'En attente',
        'paid' => 'Payé',
        'failed' => 'Échoué',
        'cancelled' => 'Annulé',
    ],

    'support_access_durations' => [
        '30m' => '30 minutes',
        '1h'  => '1 heure',
        '24h' => '24 heures',
    ],
];
