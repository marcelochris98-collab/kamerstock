<?php

return [
    'default_trial_days' => 14,
    'grace_period_days' => 5,
    'read_only_after_days' => 6,
    'suspension_after_days' => 11,
    'default_currency' => 'FCFA',
    'tenancy_strategy' => 'database_per_tenant',

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
