<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseManager
{
    protected ?string $previousConnection = null;

    /**
     * Configure the database connections for a resolved tenant.
     */
    public function configureForTenant(Tenant $tenant): void
    {
        if (!$this->canUseTenantDatabase($tenant)) {
            $this->switchToDefault();
            return;
        }

        try {
            $this->switchToTenant($tenant);
            Log::info("Connexion dynamique 'tenant' configurée pour le tenant : {$tenant->slug}");
        } catch (\Throwable $e) {
            Log::error("Erreur de connexion base de données pour le tenant {$tenant->slug}. Voir configuration tenant.");
            $this->switchToDefault();
        }
    }

    /**
     * Fallback configuration to default database for tenant connection.
     */
    public function useDefaultDatabase(): void
    {
        $defaultConnection = config('database.default', 'mysql');
        $defaultConfig = config("database.connections.{$defaultConnection}");

        Config::set('database.connections.tenant', $defaultConfig);
        DB::purge('tenant');

        if (!app()->environment('testing')) {
            DB::setDefaultConnection($defaultConnection);
        }
    }

    /**
     * Switch the active database connection to the tenant connection.
     */
    public function switchToTenant(Tenant $tenant): void
    {
        $this->previousConnection = DB::getDefaultConnection();

        $config = $this->getTenantConnectionConfig($tenant);
        Config::set('database.connections.tenant', $config);

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');
    }

    /**
     * Restore the default database connection after tenant processing.
     */
    public function switchToDefault(): void
    {
        $defaultConnection = $this->previousConnection ?? config('database.default', 'mysql');
        $defaultConfig = config("database.connections.{$defaultConnection}");

        Config::set('database.connections.tenant', $defaultConfig);
        DB::purge('tenant');
        DB::setDefaultConnection($defaultConnection);
        $this->previousConnection = null;
    }

    /**
     * Get the current connection name.
     */
    public function currentConnectionName(): string
    {
        return DB::getDefaultConnection();
    }

    /**
     * Check if a tenant database is ready to be configured and used.
     */
    public function canUseTenantDatabase(Tenant $tenant): bool
    {
        if (!$tenant) {
            return false;
        }

        if (!config('platform.tenancy_enabled', false)) {
            return false;
        }

        if (!config('platform.tenant_resolution_enabled', true)) {
            return false;
        }

        if (!config('platform.tenant_database_switching.enabled', false)) {
            return false;
        }

        if (app()->environment('local', 'testing') && !config('platform.tenant_database_switching.allow_local', true)) {
            return false;
        }

        $legacyStatus = config('platform.legacy_current_database_status', 'legacy_current_db');
        if ($tenant->provisioning_status === $legacyStatus) {
            return false;
        }

        if ($tenant->provisioning_status !== 'migrated') {
            return false;
        }

        if (empty($tenant->database_name)) {
            return false;
        }

        if ($tenant->provisioning_error) {
            return false;
        }

        if (config('platform.tenant_database_switching.switch_only_migrated', true) && $tenant->provisioning_status !== 'migrated') {
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

        $config = array_merge($defaultConfig, [
            'driver' => $defaultConfig['driver'] ?? 'mysql',
            'charset' => $defaultConfig['charset'] ?? 'utf8mb4',
            'collation' => $defaultConfig['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix' => $defaultConfig['prefix'] ?? '',
            'foreign_key_constraints' => $defaultConfig['foreign_key_constraints'] ?? true,
        ]);

        if ($config['driver'] === 'sqlite') {
            if ($tenant->database_name === ':memory:') {
                $config['database'] = ':memory:';
            } else {
                $config['database'] = database_path("tenants/{$tenant->database_name}.sqlite");
            }

            $config['url'] = null;
            $config['host'] = null;
            $config['port'] = null;
            $config['username'] = null;
            $config['password'] = null;
        } else {
            $config['database'] = $tenant->database_name;
            $config['host'] = $tenant->database_host ?: config('platform.database_provisioning.default_host', env('DB_HOST', '127.0.0.1'));
            $config['port'] = $tenant->database_port ?: config('platform.database_provisioning.default_port', env('DB_PORT', '3306'));
            $config['username'] = $tenant->database_username ?: config('platform.database_provisioning.default_username', env('DB_USERNAME', 'root'));
            $config['password'] = $tenant->database_password ? decrypt($tenant->database_password) : (config('platform.database_provisioning.default_password', env('DB_PASSWORD', '')));
        }

        unset($config['url']);

        return $config;
    }
}
