<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Services\Platform\TenantProvisioningService;
use Illuminate\Console\Command;

class MigrateTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:migrate-tenant {slug} {--force : Confirmer la migration tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lancer les migrations et créer le compte propriétaire d\'une boutique tenant.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $slug = $this->argument('slug');
        $force = $this->option('force');

        // Chercher le tenant par slug
        $tenant = Tenant::where('slug', $slug)->first();
        if (!$tenant) {
            $this->error('[ERREUR] Boutique introuvable.');
            return 1;
        }

        // Refuser legacy_current_db
        if ($tenant->provisioning_status === 'legacy_current_db') {
            $this->warn('[ATTENTION] La boutique actuelle legacy ne doit pas être migrée avec cette commande.');
            return 1;
        }

        // Vérifier la base de données
        if (empty($tenant->database_name)) {
            $this->error('[ERREUR] Aucun nom de base de données n\'est défini.');
            return 1;
        }

        // Refuser prepared
        if ($tenant->provisioning_status === 'prepared') {
            $this->warn('[ATTENTION] La base n\'est pas encore créée. Lancez d\'abord platform:provision-tenant.');
            return 1;
        }

        // Refuser failed
        if ($tenant->provisioning_status === 'failed') {
            $this->warn('[ATTENTION] Le tenant est en échec de provisionnement. Corrigez l\'erreur avant migration.');
            return 1;
        }

        // Si déjà migré
        if ($tenant->provisioning_status === 'migrated') {
            $this->info('[INFO] Cette boutique est déjà migrée.');
            return 0;
        }

        // Vérifier si les migrations sont activées
        if (!config('platform.tenant_migrations.enabled', false) && !config('platform.enable_database_provisioning', false)) {
            $this->warn('[ATTENTION] Les migrations tenant sont désactivées.');
            $this->warn('Activez PLATFORM_ENABLE_TENANT_MIGRATIONS=true pour lancer les migrations.');
            return 0;
        }

        // Demander confirmation si nécessaire
        if (!$force) {
            $this->info("Boutique : {$tenant->name}");
            $this->info("Base de données : {$tenant->database_name}");
            $this->info("Propriétaire : {$tenant->owner_login_email}");
            $this->warn('Cette action va créer les tables métier et le compte propriétaire.');

            if (!$this->confirm('Confirmer la migration ?')) {
                return 0;
            }
        }

        try {
            $service = new TenantProvisioningService();
            $service->migrateTenant($tenant);
            $tenant->refresh();

            $this->info('[OK] Migrations tenant terminées.');
            $this->info('[OK] Compte propriétaire créé ou mis à jour.');
            $this->info("[INFO] Statut : {$tenant->provisioning_status}");

            return 0;

        } catch (\Exception $e) {
            $this->error('[ERREUR] La migration tenant a échoué.');
            $this->error($e->getMessage());

            return 1;
        }
    }
}
