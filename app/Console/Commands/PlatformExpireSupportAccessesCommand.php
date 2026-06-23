<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Platform\SupportAccessService;

class PlatformExpireSupportAccessesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:expire-support-accesses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expirer automatiquement les accès support dont le temps imparti est écoulé';

    /**
     * Execute the console command.
     */
    public function handle(SupportAccessService $service): int
    {
        $this->info("Nettoyage des accès support expirés...");

        $expiredCount = $service->expireOldAccesses();

        if ($expiredCount > 0) {
            $this->info("[OK] {$expiredCount} accès support actif(s) expiré(s) et archivé(s).");
        } else {
            $this->line("Aucun accès support expiré à nettoyer.");
        }

        return Command::SUCCESS;
    }
}
