<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Services\Platform\TenantBackupService;

class BackupTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'platform:backup-tenant {tenantSlug}';

    /**
     * The console command description.
     */
    protected $description = 'Créer une sauvegarde de la base de données d’un tenant par son slug';

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
        $slug = $this->argument('tenantSlug');
        $tenant = Tenant::on('landlord')->where('slug', $slug)->first();

        if (!$tenant) {
            $this->error("Boutique introuvable avec le slug : {$slug}");
            return Command::FAILURE;
        }

        $this->info("Début de la sauvegarde de la boutique : {$tenant->name}...");

        if (!$this->backupService->canBackupTenant($tenant)) {
            $this->error("Impossible de sauvegarder : la base de données de cette boutique n'est pas encore provisionnée.");
            return Command::FAILURE;
        }

        try {
            $backup = $this->backupService->runManualBackup($tenant);

            if ($backup->isCompleted()) {
                $this->info("[OK] Sauvegarde créée avec succès !");
                $this->line("Tenant : {$tenant->name}");
                $this->line("Statut : completed");
                $this->line("Fichier : {$backup->filename}");
                $this->line("Taille : " . $backup->sizeForHumans());
                return Command::SUCCESS;
            } else {
                $this->error("[FAILED] La sauvegarde a échoué.");
                $this->error("Erreur : " . ($backup->error_message ?: 'Erreur inconnue'));
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("[FAILED] Une exception est survenue : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
