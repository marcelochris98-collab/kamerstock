<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Services\Platform\TenantBackupService;

class BackupAllTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'platform:backup-all-tenants';

    /**
     * The console command description.
     */
    protected $description = 'Lancer la sauvegarde de toutes les boutiques éligibles (actives, trial, non archivées, prêtes)';

    protected TenantBackupService $backupService;

    public function __construct(TenantBackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = Tenant::on('landlord')
            ->whereIn('status', ['active', 'trial'])
            ->whereNotIn('provisioning_status', ['prepared']) // Exclude not provisioned database
            ->get();

        if ($tenants->isEmpty()) {
            $this->info("Aucune boutique éligible pour la sauvegarde.");
            return Command::SUCCESS;
        }

        $this->info("Démarrage de la sauvegarde de " . $tenants->count() . " boutique(s)...");

        $successCount = 0;
        $failedCount = 0;
        $summary = [];

        foreach ($tenants as $tenant) {
            $this->line("Sauvegarde de la boutique : {$tenant->name} ({$tenant->slug})...");

            try {
                // automatic type for cron jobs or scripted all-backups
                $backup = $this->backupService->createBackupRecord($tenant, 'automatic', ['mode' => config('platform.backups.enabled') ? 'real' : 'simulation']);
                $backup = $this->backupService->runBackup($backup);

                if ($backup->isCompleted()) {
                    $this->info("  -> [OK] Réussie.");
                    $successCount++;
                    $summary[] = [
                        'tenant' => $tenant->name,
                        'status' => 'OK',
                        'detail' => $backup->sizeForHumans()
                    ];
                } else {
                    $this->error("  -> [FAILED] Échouée : " . $backup->error_message);
                    $failedCount++;
                    $summary[] = [
                        'tenant' => $tenant->name,
                        'status' => 'FAIL',
                        'detail' => $backup->error_message
                    ];
                }
            } catch (\Throwable $e) {
                $this->error("  -> [FAILED] Exception : " . $e->getMessage());
                $failedCount++;
                $summary[] = [
                    'tenant' => $tenant->name,
                    'status' => 'FAIL',
                    'detail' => $e->getMessage()
                ];
            }
        }

        $this->line("\n=== Résumé des Sauvegardes ===");
        $this->line("Boutiques réussies : {$successCount}");
        $this->line("Boutiques échouées : {$failedCount}");

        foreach ($summary as $item) {
            $color = $item['status'] === 'OK' ? 'info' : 'error';
            $this->$color("  - {$item['tenant']} : [{$item['status']}] {$item['detail']}");
        }

        return $failedCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
