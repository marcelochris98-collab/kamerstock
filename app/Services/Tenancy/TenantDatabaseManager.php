<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseManager
{
    /**
     * Configure the database connections for a resolved tenant.
     */
    public function configureForTenant(Tenant $tenant): void
    {
        $status = $tenant->provisioning_status;
        $legacyStatus = config('platform.legacy_current_database_status', 'legacy_current_db');

        // Case 1: Legacy Tenant (shares current database)
        if ($status === $legacyStatus) {
            $this->useDefaultDatabase();
            return;
        }

        // Case 2: Pending/Prepared Tenant (not yet provisioned)
        if ($this->isPreparedOnly($tenant)) {
            $this->useDefaultDatabase();
            return;
        }

        // Case 3: Database is ready (created or migrated) and satisfies all requirements
        if ($this->canUseTenantDatabase($tenant)) {
            try {
                $config = $this->getTenantConnectionConfig($tenant);
                Config::set('database.connections.tenant', $config);

                // Purge Laravel internal cache for the tenant connection to apply changes
                DB::purge('tenant');
                
                if (!app()->environment('testing')) {
                    DB::reconnect('tenant');
                }

                // If global tenancy mode is active, set tenant as default connection
                if (config('platform.tenancy_enabled', false) && !app()->environment('testing')) {
                    DB::setDefaultConnection('tenant');
                }

                Log::info("Connexion dynamique 'tenant' configurée pour le tenant : {$tenant->slug}");
            } catch (\Throwable $e) {
                Log::error("Erreur de connexion base de données pour le tenant {$tenant->slug} : " . $e->getMessage());
                // Fallback to default database
                $this->useDefaultDatabase();
            }
        } else {
            $this->useDefaultDatabase();
        }
    }

    /**
     * Fallback configuration to default database for tenant connection.
     */
    public function useDefaultDatabase(): void
    {
        $defaultConnection = config('database.default', 'mysql');
        $defaultConfig = config("database.connections.{$defaultConnection}");

        // Point 'tenant' connection to standard mysql database configuration
        Config::set('database.connections.tenant', $defaultConfig);
        DB::purge('tenant');

        if (config('platform.tenancy_enabled', false) && !app()->environment('testing')) {
            DB::setDefaultConnection($defaultConnection);
        }
    }

    /**
     * Check if a tenant database is ready to be configured and used.
     */
    public function canUseTenantDatabase(Tenant $tenant): bool
    {
        $readyStatuses = config('platform.tenant_ready_statuses', ['legacy_current_db', 'database_created', 'migrated']);
        
        // Must be in ready status list
        if (!in_array($tenant->provisioning_status, $readyStatuses, true)) {
            return false;
        }

        // Legacy tenant uses default db, not dynamic tenant db connection
        if ($tenant->provisioning_status === config('platform.legacy_current_database_status', 'legacy_current_db')) {
            return false;
        }

        // Host, name and username must exist
        if (empty($tenant->database_name) || empty($tenant->database_host) || empty($tenant->database_username)) {
            return false;
        }

        // No critical provisioning error
        if ($tenant->provisioning_error) {
            return false;
        }

        return true;
    }

    /**
     * Check if a tenant is prepared/pending but not fully provisioned.
     */
    public function isPreparedOnly(Tenant $tenant): bool
    {
        $pendingStatuses = config('platform.tenant_pending_statuses', ['prepared', 'pending']);
        return in_array($tenant->provisioning_status, $pendingStatuses, true);
    }

    /**
     * Check if a tenant database is ready to be configured.
     */
    public function isTenantDatabaseReady(Tenant $tenant): bool
    {
        $readyStatuses = config('platform.tenant_ready_statuses', ['legacy_current_db', 'database_created', 'migrated']);
        return in_array($tenant->provisioning_status, $readyStatuses, true);
    }

    /**
     * Build the tenant connection settings array.
     */
    public function getTenantConnectionConfig(Tenant $tenant): array
    {
        $defaultConnection = config('database.default', 'mysql');
        $defaultConfig = config("database.connections.{$defaultConnection}", []);

        // Decrypt password safely
        $password = null;
        if ($tenant->database_password) {
            try {
                $password = decrypt($tenant->database_password);
            } catch (\Exception $e) {
                // Return plain text if not encrypted or decrypter fails
                $password = $tenant->database_password;
            }
        }

        $config = array_merge($defaultConfig, [
            'host' => $tenant->database_host ?: env('DB_HOST', '127.0.0.1'),
            'port' => $tenant->database_port ?: env('DB_PORT', '3306'),
            'database' => $tenant->database_name,
            'username' => $tenant->database_username ?: env('DB_USERNAME', 'root'),
            'password' => $password,
        ]);

        unset($config['url']);

        return $config;
    }
}
