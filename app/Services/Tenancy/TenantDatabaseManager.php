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
        $pendingStatuses = config('platform.tenant_pending_statuses', ['prepared', 'pending']);
        if (in_array($status, $pendingStatuses, true)) {
            $this->useDefaultDatabase();
            return;
        }

        // Case 3: Database is ready (created or migrated) and database name is defined
        $readyStatuses = config('platform.tenant_ready_statuses', ['database_created', 'migrated']);
        if (in_array($status, $readyStatuses, true) && $tenant->database_name) {
            try {
                $config = $this->getTenantConnectionConfig($tenant);
                Config::set('database.connections.tenant', $config);

                // Purge Laravel internal cache for the tenant connection to apply changes
                DB::purge('tenant');
                DB::reconnect('tenant');

                // If global tenancy mode is active, set tenant as default connection
                if (config('platform.tenancy_enabled', false)) {
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

        if (config('platform.tenancy_enabled', false)) {
            DB::setDefaultConnection($defaultConnection);
        }
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
