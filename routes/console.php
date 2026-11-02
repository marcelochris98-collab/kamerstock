<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// =========================================================================
// Tâches Planifiées Plateforme Multi-Tenant (CRON)
// =========================================================================
use Illuminate\Support\Facades\Schedule;

// 1. Vérification quotidienne des dates d'essais et abonnements (passage en read_only / suspended)
Schedule::command('platform:update-tenant-statuses')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/tenant-statuses.log'));

// 2. Expiration automatique des accès support technique Landlord échus
Schedule::command('platform:expire-support-accesses')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

// 3. Sauvegarde automatique nocturne de toutes les bases boutiques éligibles
Schedule::command('platform:backup-all-tenants')
    ->dailyAt('02:30')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/tenant-backups.log'));

// 4. Nettoyage et rotation des sauvegardes selon la politique de rétention
Schedule::command('platform:cleanup-backups')
    ->dailyAt('04:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/tenant-backups-cleanup.log'));

