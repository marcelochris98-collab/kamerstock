<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Services\Platform\TenantBackupService;

class CleanupBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'platform:cleanup-backups';

    /**
     * The console command description.
     */
    protected $description = 'Conserver uniquement les N dernières sauvegardes par boutique et supprimer les anciennes';

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
        $tenants = Tenant::on('landlord')->get();
        $keep = config('platform.backups.keep_last', 10);
        $this->info("Nettoyage des sauvegardes en conservant les {$keep} dernières par boutique...");

        $totalCleaned = 0;

        foreach ($tenants as $tenant) {
            $cleaned = $this->backupService->cleanupOldBackups($tenant);
            if ($cleaned > 0) {
                $this->info("Boutique '{$tenant->name}' : {$cleaned} sauvegarde(s) supprimée(s).");
                $totalCleaned += $cleaned;
            }
        }

        $this->info("Nettoyage terminé. Total de sauvegardes nettoyées : {$totalCleaned}");
        return Command::SUCCESS;
    }
}
