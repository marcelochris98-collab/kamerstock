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
     * Retourne si le provisionnement de base de données réel est activé.
     * Supporte l'ancienne clé `platform.enable_database_provisioning`
     * et la nouvelle `platform.database_provisioning.enabled`.
     */
    private function isDbProvisioningEnabled(): bool
    {
        // Retourne true si l'une des deux clés de configuration est activée.
        return (bool) (
            config('platform.database_provisioning.enabled', false) ||
            config('platform.enable_database_provisioning', false)
        );
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

        if (!$this->isDbProvisioningEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Create the physical database (MySQL or SQLite).
     */
    public function createDatabase(Tenant $tenant): bool
    {
        // Sécurité : refuser explicitement la boutique legacy
        if ($tenant->provisioning_status === 'legacy_current_db' || $tenant->slug === 'legacy_current_db') {
            throw new Exception('Provisionnement refusé pour la boutique legacy_current_db.');
        }

        $dbName = $tenant->database_name;
        $cleanDbName = preg_replace('/[^A-Za-z0-9_]/', '', (string) $dbName);

        if (empty($cleanDbName)) {
            throw new Exception('Nom de base de données invalide après nettoyage. Autorisé : lettres, chiffres et underscores.');
        }

        $driver = DB::connection('landlord')->getDriverName();

        if ($driver === 'sqlite') {
            $path = database_path("tenants/{$cleanDbName}.sqlite");
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            if (!file_exists($path)) {
                touch($path);
            }
            return true;
        }

        // MySQL database creation — utilisation de l'identifiant nettoyé
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
        $sensitive = [];
        $sensitive[] = config('database.connections.landlord.password', '');
        $sensitive[] = config('database.connections.mysql.password', '');
        if (!empty(config('platform.database_provisioning.default_password', ''))) {
            $sensitive[] = config('platform.database_provisioning.default_password');
        }
        if (!empty($tenant->database_password)) {
            $sensitive[] = $tenant->database_password;
        }
        if (!empty($tenant->owner_password_plain)) {
            $sensitive[] = $tenant->owner_password_plain;
        }

        $cleanMessage = $message;
        foreach (array_unique(array_filter($sensitive)) as $secret) {
            $cleanMessage = str_ireplace($secret, '[MASQUE]', $cleanMessage);
        }

        $tenant->update([
            'provisioning_status' => 'failed',
            'provisioning_error' => $cleanMessage,
        ]);

        // Journaliser de façon contrôlée via LandlordAuditService si disponible
        try {
            LandlordAuditService::record(
                'tenant_database_creation_failed',
                $tenant,
                "Échec de création de base de données pour la boutique : {$tenant->name}. Erreur technique enregistrée."
            );
        } catch (Exception $e) {
            // ne pas propager l'erreur d'audit
        }

        return $tenant;
    }

    /**
     * Provision the actual database for the tenant (Mode B).
     */
    public function provisionDatabase(Tenant $tenant): Tenant
    {
        // Si le provisionnement réel est désactivé, on ne fait rien et on conserve le statut 'prepared'
        if (!$this->isDbProvisioningEnabled()) {
            // ne pas modifier le statut si déjà 'prepared'
            if ($tenant->provisioning_status !== 'prepared') {
                $tenant->update(['provisioning_status' => 'prepared']);
            }
            return $tenant;
        }

        // Protéger la boutique legacy
        if ($tenant->provisioning_status === 'legacy_current_db' || $tenant->slug === 'legacy_current_db') {
            return $tenant;
        }

        // Vérifications préalables
        if (empty($tenant->database_name)) {
            return $this->markProvisioningFailed($tenant, 'Aucun nom de base de données défini pour cette boutique.');
        }

        try {
            $this->createDatabase($tenant);
            $this->markDatabaseCreated($tenant);
        } catch (Exception $e) {
            // Ne jamais logger des messages bruts contenant potentiellement des secrets
            try {
                $this->markProvisioningFailed($tenant, $e->getMessage());
            } catch (Exception $inner) {
                // dernier recours : mettre en failed sans message sensible
                $tenant->update([
                    'provisioning_status' => 'failed',
                    'provisioning_error' => 'Erreur inconnue lors du provisionnement.'
                ]);
            }
        }

        return $tenant;
    }

    /**
     * Retourne si les migrations tenant réelles sont activées.
     * Supporte l'ancienne clé `platform.enable_database_provisioning` pour compatibilité.
     */
    private function isTenantMigrationEnabled(): bool
    {
        $enabled = (bool) (
            config('platform.tenant_migrations.enabled', false) ||
            config('platform.enable_database_provisioning', false) ||
            config('platform.database_provisioning.enabled', false)
        );

        if (!$enabled) {
            return false;
        }

        if (app()->environment('local') && !config('platform.tenant_migrations.allow_local', true)) {
            return false;
        }

        return true;
    }

    /**
     * Configure la connexion tenant à partir du tenant.
     */
    private function configureTenantConnection(Tenant $tenant): void
    {
        $dbName = $tenant->database_name;
        $driver = DB::connection('landlord')->getDriverName();

        $tenantConfig = [
            'driver' => $driver,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ];

        if ($driver === 'sqlite') {
            $tenantConfig['database'] = database_path("tenants/{$dbName}.sqlite");
        } else {
            $tenantConfig['database'] = $dbName;
            $tenantConfig['host'] = $tenant->database_host ?? config('platform.database_provisioning.default_host', '127.0.0.1');
            $tenantConfig['port'] = $tenant->database_port ?? config('platform.database_provisioning.default_port', '3306');
            $tenantConfig['username'] = $tenant->database_username ?? config('platform.database_provisioning.default_username', 'root');
            $tenantConfig['password'] = $tenant->database_password ?? config('platform.database_provisioning.default_password', '');
        }

        config(['database.connections.tenant' => $tenantConfig]);
    }

    /**
     * Restaure la connexion par défaut.
     */
    private function restoreDefaultConnection(?string $previousDefault): void
    {
        if ($previousDefault) {
            config(['database.default' => $previousDefault]);
        }
        DB::purge('tenant');
    }

    /**
     * Masque les informations sensibles du message d'erreur.
     */
    private function sanitizeProvisioningError(string $message, Tenant $tenant): string
    {
        $sensitive = [];

        // Ajouter les mots de passe potentiels
        $sensitive[] = $tenant->database_password ?? '';
        $sensitive[] = $tenant->owner_password_plain ?? '';
        $sensitive[] = config('database.connections.landlord.password', '');
        $sensitive[] = config('database.connections.mysql.password', '');
        $sensitive[] = config('platform.database_provisioning.default_password', '');
        $sensitive[] = env('DB_PASSWORD', '');
        $sensitive[] = env('TENANT_DB_PASSWORD', '');

        $cleanMessage = $message;
        foreach (array_unique(array_filter($sensitive)) as $secret) {
            if (!empty($secret)) {
                $cleanMessage = str_ireplace($secret, '[MASQUE]', $cleanMessage);
            }
        }

        return $cleanMessage;
    }

    /**
     * Run migrations in the tenant database.
     */
    public function runTenantMigrations(Tenant $tenant): Tenant
    {
        if (!$this->isTenantMigrationEnabled()) {
            return $tenant;
        }

        // Refuser les boutiques spéciales
        if ($tenant->provisioning_status === 'legacy_current_db') {
            throw new Exception('Les migrations ne peuvent pas être lancées sur la boutique legacy.');
        }

        // Refuser les tenants en échec
        if ($tenant->provisioning_status === 'failed') {
            throw new Exception('Le tenant est en état d\'échec. Corrigez le problème avant les migrations.');
        }

        // Vérifier que la base existe
        if ($tenant->provisioning_status === 'prepared') {
            throw new Exception('La base de données doit être créée avant les migrations.');
        }

        // Vérifier le nom de base de données
        if (empty($tenant->database_name)) {
            throw new Exception('Aucun nom de base de données n\'est défini.');
        }

        $previousDefault = config('database.default');

        try {
            $this->configureTenantConnection($tenant);
            DB::purge('tenant');

            // Lancer les migrations
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            // Lancer les seeders si activés
            if (config('platform.tenant_migrations.run_seeders', true)) {
                config(['database.default' => 'tenant']);
                DB::purge('tenant');

                Artisan::call('db:seed', [
                    '--class' => 'RoleAndPermissionSeeder',
                    '--database' => 'tenant',
                ]);
            }

            $this->restoreDefaultConnection($previousDefault);
            return $tenant;

        } catch (Exception $e) {
            $this->restoreDefaultConnection($previousDefault);
            throw $e;
        }
    }

    /**
     * Create default tenant owner user in tenant DB.
     */
    public function createTenantOwner(Tenant $tenant, ?string $password = null): Tenant
    {        if (!$this->isTenantMigrationEnabled()) {
            return $tenant;
        }
        // Refuser les boutiques spéciales
        if ($tenant->provisioning_status === 'legacy_current_db') {
            throw new Exception('Le propriétaire ne peut pas être créé pour la boutique legacy.');
        }

        // Refuser les tenants en echec
        if ($tenant->provisioning_status === 'failed') {
            throw new Exception('Le tenant est en état d\'échec. Corrigez le problème avant de créer le propriétaire.');
        }

        // Vérifier l'email propriétaire
        if (empty($tenant->owner_login_email)) {
            throw new Exception('Aucun email propriétaire n\'est défini.');
        }

        // Utiliser le mot de passe fourni ou celui du tenant
        $ownerPassword = $password ?? $tenant->owner_password_plain;
        if (empty($ownerPassword)) {
            throw new Exception('Aucun mot de passe propriétaire n\'est défini.');
        }

        $previousDefault = config('database.default');

        try {
            $this->configureTenantConnection($tenant);
            DB::purge('tenant');

            config(['database.default' => 'tenant']);
            DB::purge('tenant');

            // Chercher le rôle propriétaire
            $ownerRoleSlug = config('platform.tenant_migrations.default_owner_role_slug', 'admin');
            $adminRole = Role::where('slug', $ownerRoleSlug)->first();
            if (!$adminRole) {
                throw new Exception("Le rôle '{$ownerRoleSlug}' n\'existe pas dans la base tenant.");
            }

            // Créer ou mettre à jour l'utilisateur propriétaire
            $user = User::updateOrCreate(
                ['email' => $tenant->owner_login_email],
                [
                    'name' => $tenant->owner_name ?? 'Administrateur',
                    'password' => Hash::make($ownerPassword),
                    'role_id' => $adminRole->id,
                    'is_active' => true,
                    'notifications_enabled' => true,
                    'sounds_enabled' => true,
                ]
            );

            $this->restoreDefaultConnection($previousDefault);
            return $tenant;

        } catch (Exception $e) {
            $this->restoreDefaultConnection($previousDefault);
            throw $e;
        }
    }

    /**
     * Migrate a tenant database and create owner account.
     * Orchestrates the full migration process.
     */
    public function migrateTenant(Tenant $tenant): Tenant
    {
        if (!$this->isTenantMigrationEnabled()) {
            throw new Exception('Les migrations tenant sont désactivées.');
        }

        // Refuser les boutiques spéciales
        if ($tenant->provisioning_status === 'legacy_current_db') {
            throw new Exception('La boutique legacy ne doit pas être migrée.');
        }

        // Vérifier le nom de base de données
        if (empty($tenant->database_name)) {
            throw new Exception('Aucun nom de base de données n\'est défini.');
        }

        // Refuser si encore en prepared
        if ($tenant->provisioning_status === 'prepared') {
            throw new Exception('La base de données doit être créée avant les migrations. Lancez d\'abord le provisionnement.');
        }

        // Si déjà migré, ne rien faire
        if ($tenant->provisioning_status === 'migrated') {
            return $tenant;
        }

        try {
            // Lancer les migrations
            $this->runTenantMigrations($tenant);

            // Créer le compte propriétaire
            $this->createTenantOwner($tenant);

            // Marquer comme migré
            $tenant->update([
                'provisioning_status' => 'migrated',
                'provisioning_error' => null,
            ]);

            LandlordAuditService::record(
                'tenant_migrated',
                $tenant,
                "Boutique migrée avec succès : {$tenant->name}"
            );

            return $tenant;

        } catch (Exception $e) {
            $cleanError = $this->sanitizeProvisioningError($e->getMessage(), $tenant);
            $tenant->update([
                'provisioning_status' => 'failed',
                'provisioning_error' => $cleanError,
            ]);

            LandlordAuditService::record(
                'tenant_migration_failed',
                $tenant,
                "Échec de la migration : {$tenant->name}. Erreur technique enregistrée."
            );

            throw $e;
        }
    }

    /**
     * Run entire provisioning sequence.
     */
    public function provision(Tenant $tenant): void
    {
        if (!$this->isDbProvisioningEnabled()) {
            $tenant->update([
                'provisioning_status' => 'prepared'
            ]);
            return;
        }

        try {
            $this->provisionDatabase($tenant);
            $tenant->refresh();

            if ($this->isTenantMigrationEnabled()) {
                // Lancer les migrations
                $this->runTenantMigrations($tenant);

                // Créer le propriétaire
                $this->createTenantOwner($tenant, $tenant->owner_password_plain);

                // Mettre à jour le statut final
                $tenant->update([
                    'provisioning_status' => 'migrated',
                    'provisioning_error' => null,
                ]);
            }

        } catch (Exception $e) {
            $cleanError = $this->sanitizeProvisioningError($e->getMessage(), $tenant);
            $tenant->update([
                'provisioning_status' => 'failed',
                'provisioning_error' => $cleanError,
            ]);

            logger()->error('Tenant provisioning failed: ' . $e->getMessage(), [
                'tenant_id' => $tenant->id,
                'exception' => $e
            ]);
        }
    }
}
