<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class RegisterCurrentTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'platform:register-current-tenant';

    /**
     * The console command description.
     */
    protected $description = 'Enregistre la boutique actuelle (mono-boutique) dans la table platform_tenants en tant que legacy_current_db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Début de l'enregistrement de la boutique actuelle...");

        $slug = 'boutique-actuelle';

        // Check if already registered
        $tenant = Tenant::on('landlord')->where('slug', $slug)->first();

        if ($tenant) {
            $this->info("La boutique actuelle est déjà enregistrée en tant que tenant legacy ('{$slug}').");
            return 0;
        }

        // Retrieve current business type from local settings table
        $businessType = 'quincaillerie';
        $businessTypeCustom = null;

        try {
            $settings = Setting::first();
            if ($settings) {
                $businessType = $settings->business_type ?: 'quincaillerie';
                $businessTypeCustom = $settings->business_type_custom;
            }
        } catch (\Exception $e) {
            $this->warn("Impossible de charger les paramètres de boutique métier: " . $e->getMessage() . ". Utilisation des valeurs par défaut.");
        }

        // Retrieve default database settings
        $defaultConnection = config('database.default', 'mysql');
        $dbConfig = config("database.connections.{$defaultConnection}", []);

        $dbName = $dbConfig['database'] ?? env('DB_DATABASE', 'kamerstock');
        $dbHost = $dbConfig['host'] ?? env('DB_HOST', '127.0.0.1');
        $dbPort = $dbConfig['port'] ?? env('DB_PORT', '3306');
        $dbUser = $dbConfig['username'] ?? env('DB_USERNAME', 'root');
        $dbPassword = $dbConfig['password'] ?? env('DB_PASSWORD', '');

        // Encrypt the current DB password to store securely
        $encryptedPassword = $dbPassword ? encrypt($dbPassword) : null;

        // Register tenant
        $tenant = Tenant::on('landlord')->create([
            'name' => 'Boutique actuelle',
            'slug' => $slug,
            'status' => 'active',
            'provisioning_status' => config('platform.legacy_current_database_status', 'legacy_current_db'),
            'database_name' => $dbName,
            'database_host' => $dbHost,
            'database_port' => $dbPort,
            'database_username' => $dbUser,
            'database_password' => $encryptedPassword,
            'business_type' => $businessType,
            'business_type_custom' => $businessTypeCustom,
            'owner_email' => 'admin@kamerstock.cm',
            'notes' => 'Boutique historique créée avant le passage au multi-tenant',
        ]);

        $this->info("La boutique actuelle a été enregistrée avec succès !");
        $this->line("Détails: Name: {$tenant->name} | Slug: {$tenant->slug} | DB Name: {$tenant->database_name} | Status: {$tenant->provisioning_status}");

        return 0;
    }
}
