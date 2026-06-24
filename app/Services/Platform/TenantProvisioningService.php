<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Services\Platform\LandlordAuditService;
use Carbon\Carbon;
use Exception;

class TenantProvisioningService
{
    /**
     * Generate unique slug from the tenant name.
     */
    public function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;
        
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    /**
     * Generate database name from the slug.
     */
    public function generateDatabaseName(string $slug): string
    {
        $prefix = config('platform.tenant_database_prefix', 'kamerstock_tenant_');
        return $prefix . str_replace('-', '_', $slug);
    }

    /**
     * Generate random temporary password.
     */
    public function generateTemporaryPassword(int $length = 10): string
    {
        return Str::random($length);
    }

    /**
     * Prepare initial data for the tenant model.
     */
    public function prepareTenantData(array $data): array
    {
        $data['uuid'] = (string) Str::uuid();
        
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }
        
        $data['database_name'] = $data['database_name'] ?? $this->generateDatabaseName($data['slug']);
        $data['database_username'] = $data['database_username'] ?? 'root';
        $data['database_password'] = $data['database_password'] ?? '';
        $data['database_host'] = $data['database_host'] ?? '127.0.0.1';
        $data['database_port'] = $data['database_port'] ?? '3306';
        
        $data['timezone'] = $data['timezone'] ?? 'Africa/Douala';
        $data['currency'] = $data['currency'] ?? 'FCFA';
        $data['provisioning_status'] = 'prepared';

        $data['owner_login_email'] = $data['owner_email'] ?? null;
        
        $passLen = config('platform.tenant_default_password_length', 10);
        $data['owner_password_plain'] = $this->generateTemporaryPassword($passLen);
        $data['owner_login_password_generated_at'] = now();

        return $data;
    }

    /**
     * Create and save the tenant model.
     */
    public function createTenant(array $data): Tenant
    {
        $prepared = $this->prepareTenantData($data);
        return Tenant::create($prepared);
    }

    /**
     * Create initial subscription based on plan selection.
     */
    public function createInitialSubscription(Tenant $tenant, ?Plan $plan, array $data): ?Subscription
    {
        if (!$plan) {
            return null;
        }

        $trialDays = isset($data['trial_days']) ? intval($data['trial_days']) : null;
        $subMonths = isset($data['subscription_months']) ? intval($data['subscription_months']) : null;

        $startsAt = now();
        $trialEndsAt = null;
        $endsAt = null;

        $status = $data['status'] ?? 'trial';

        if ($status === 'trial') {
            $trialDays = $trialDays ?? config('platform.default_trial_days', 14);
            $trialEndsAt = now()->addDays($trialDays);
            $endsAt = $trialEndsAt;
        } else {
            $months = $subMonths ?? 1;
            $endsAt = (!empty($data['subscription_ends_at']))
                ? Carbon::parse($data['subscription_ends_at'])
                : now()->addMonths($months);
        }

        return Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
            'amount' => $plan->price_monthly,
            'currency' => $tenant->currency ?? 'FCFA',
            'billing_cycle' => 'monthly',
            'auto_renew' => true,
        ]);
    }

    /**
     * Check if a database can be provisioned for this tenant.
     */
    public function canProvisionDatabase(Tenant $tenant): bool
    {
        if ($tenant->provisioning_status === 'legacy_current_db') {
            return false;
        }

        if (empty($tenant->database_name)) {
            return false;
        }

        if (!config('platform.database_provisioning.enabled', false)) {
            return false;
        }

        return true;
    }

    /**
     * Create the physical database (MySQL or SQLite).
     */
    public function createDatabase(Tenant $tenant): bool
    {
        $dbName = $tenant->database_name;
        $cleanDbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
        
        if (empty($cleanDbName)) {
            throw new Exception("Nom de base de données invalide après nettoyage.");
        }

        $driver = DB::connection('landlord')->getDriverName();

        if ($driver === 'sqlite') {
            $path = database_path("tenants/{$cleanDbName}.sqlite");
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            touch($path);
            return true;
        }

        // MySQL database creation
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$cleanDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        return true;
    }

    /**
     * Mark the tenant database as successfully created.
     */
    public function markDatabaseCreated(Tenant $tenant): Tenant
    {
        $tenant->update([
            'provisioning_status' => 'database_created',
            'provisioning_error' => null,
        ]);

        LandlordAuditService::record(
            'tenant_database_created',
            $tenant,
            "Base de données créée avec succès pour la boutique : {$tenant->name}"
        );

        return $tenant;
    }

    /**
     * Mark the tenant database creation as failed.
     */
    public function markProvisioningFailed(Tenant $tenant, string $message): Tenant
    {
        // Sécurité : masquer les mots de passe potentiels
        $cleanMessage = str_ireplace(
            [
                config('database.connections.landlord.password', 'DB_PASSWORD_NOT_FOUND_LANDLORD'),
                config('database.connections.mysql.password', 'DB_PASSWORD_NOT_FOUND_MYSQL'),
                config('platform.database_provisioning.default_password', 'DB_PASSWORD_NOT_FOUND_DEFAULT'),
                $tenant->database_password,
                $tenant->owner_password_plain
            ],
            '[MASQUE]',
            $message
        );

        $tenant->update([
            'provisioning_status' => 'failed',
            'provisioning_error' => $cleanMessage,
        ]);

        LandlordAuditService::record(
            'tenant_database_creation_failed',
            $tenant,
            "Échec de création de base de données pour la boutique : {$tenant->name}. Erreur : {$cleanMessage}"
        );

        return $tenant;
    }

    /**
     * Provision the actual database for the tenant (Mode B).
     */
    public function provisionDatabase(Tenant $tenant): Tenant
    {
        if (!config('platform.database_provisioning.enabled', false)) {
            $tenant->update([
                'provisioning_status' => 'prepared'
            ]);
            return $tenant;
        }

        if ($tenant->provisioning_status === 'legacy_current_db') {
            return $tenant;
        }

        try {
            $this->createDatabase($tenant);
            $this->markDatabaseCreated($tenant);
        } catch (Exception $e) {
            $this->markProvisioningFailed($tenant, $e->getMessage());
        }

        return $tenant;
    }

    /**
     * Run migrations in the tenant database.
     */
    public function runTenantMigrations(Tenant $tenant): void
    {
        if (!config('platform.enable_database_provisioning', false)) {
            return;
        }

        if ($tenant->provisioning_status === 'failed') {
            return;
        }

        $dbName = $tenant->database_name;
        $driver = DB::connection('landlord')->getDriverName();

        try {
            config([
                'database.connections.tenant' => [
                    'driver' => $driver,
                    'database' => $driver === 'sqlite'
                        ? database_path("tenants/{$dbName}.sqlite")
                        : $dbName,
                    'host' => config('database.connections.mysql.host', '127.0.0.1'),
                    'port' => config('database.connections.mysql.port', '3306'),
                    'username' => config('database.connections.mysql.username', 'root'),
                    'password' => config('database.connections.mysql.password', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]
            ]);

            DB::purge('tenant');

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            // Run role seeder dynamically via Artisan to prevent command console errors
            $previousDefault = config('database.default');
            config(['database.default' => 'tenant']);
            DB::purge('tenant');

            Artisan::call('db:seed', [
                '--class' => 'RoleAndPermissionSeeder',
                '--database' => 'tenant',
            ]);

            config(['database.default' => $previousDefault]);

            $tenant->update([
                'provisioning_status' => 'migrated',
            ]);
        } catch (Exception $e) {
            if (isset($previousDefault)) {
                config(['database.default' => $previousDefault]);
            }
            $tenant->update([
                'provisioning_status' => 'failed',
                'provisioning_error' => 'Migrations run failed: ' . $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create default tenant owner user in tenant DB.
     */
    public function createTenantOwner(Tenant $tenant, string $password): void
    {
        if (!config('platform.enable_database_provisioning', false)) {
            return;
        }

        if ($tenant->provisioning_status === 'failed') {
            return;
        }

        $dbName = $tenant->database_name;
        $driver = DB::connection('landlord')->getDriverName();

        try {
            config([
                'database.connections.tenant' => [
                    'driver' => $driver,
                    'database' => $driver === 'sqlite'
                        ? database_path("tenants/{$dbName}.sqlite")
                        : $dbName,
                    'host' => config('database.connections.mysql.host', '127.0.0.1'),
                    'port' => config('database.connections.mysql.port', '3306'),
                    'username' => config('database.connections.mysql.username', 'root'),
                    'password' => config('database.connections.mysql.password', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ]
            ]);

            DB::purge('tenant');

            $previousDefault = config('database.default');
            config(['database.default' => 'tenant']);

            $adminRole = Role::where('slug', 'admin')->first();

            User::create([
                'name' => $tenant->owner_name ?? 'Administrateur',
                'email' => $tenant->owner_login_email,
                'password' => $password,
                'role_id' => $adminRole?->id,
                'is_active' => true,
                'notifications_enabled' => true,
                'sounds_enabled' => true,
            ]);

            config(['database.default' => $previousDefault]);
        } catch (Exception $e) {
            if (isset($previousDefault)) {
                config(['database.default' => $previousDefault]);
            }
            $tenant->update([
                'provisioning_status' => 'failed',
                'provisioning_error' => 'Owner creation failed: ' . $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Run entire provisioning sequence.
     */
    public function provision(Tenant $tenant): void
    {
        if (!config('platform.enable_database_provisioning', false)) {
            $tenant->update([
                'provisioning_status' => 'prepared'
            ]);
            return;
        }

        try {
            $this->provisionDatabase($tenant);
            $this->runTenantMigrations($tenant);
            $this->createTenantOwner($tenant, $tenant->owner_password_plain);
        } catch (Exception $e) {
            logger()->error('Tenant provisioning failed: ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'exception' => $e
            ]);
        }
    }
}
