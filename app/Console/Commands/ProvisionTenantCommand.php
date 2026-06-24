<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Services\Platform\TenantProvisioningService;
use App\Services\Platform\LandlordAuditService;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'platform:provision-tenant {slug}';

    protected $description = 'Provisionner réellement la base de données d\'une boutique tenant.';

    protected $provisioningService;

    public function __construct(TenantProvisioningService $provisioningService)
    {
        parent::__construct();
        $this->provisioningService = $provisioningService;
    }

    public function handle()
    {
        $slug = $this->argument('slug');

        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            $this->error('[ERREUR] Boutique introuvable.');
            return 1;
        }

        if ($tenant->provisioning_status === 'legacy_current_db' || $tenant->slug === 'legacy_current_db') {
            $this->warn('[ATTENTION] Cette boutique utilise la base actuelle legacy. Elle ne doit pas être provisionnée ici.');
            return 1;
        }

        if (empty($tenant->database_name)) {
            $this->warn('[ATTENTION] Aucun nom de base de données défini pour cette boutique.');
            return 1;
        }

        if (!config('platform.database_provisioning.enabled', false)) {
            $this->warn('[ATTENTION] Le provisionnement réel est désactivé.');
            $this->info('[INFO] Activez PLATFORM_ENABLE_DB_PROVISIONING=true pour créer réellement la base.');
            return 0;
        }

        $this->info("Provisionnement de la boutique : {$tenant->name} ({$tenant->slug})...");

        $result = $this->provisioningService->provisionDatabase($tenant);

        $tenant->refresh();

        if ($tenant->provisioning_status === 'database_created') {
            $this->info('[OK] Base de données créée avec succès.');
            $this->info('[INFO] Statut : database_created');
            LandlordAuditService::record('tenant_provision_command', $tenant, "Provisionnement via CLI pour : {$tenant->name}");
            return 0;
        }

        $this->error('[ERREUR] Échec du provisionnement.');
        $this->info('[INFO] Consulter provisioning_error dans le Landlord.');
        return 1;
    }
}
