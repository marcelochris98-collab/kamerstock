<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PlatformFullCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:full-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coordonne et execute l\'ensemble des commandes de diagnostic, de sante et de verification des abonnements de la plateforme.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->title('Lancement de la Verification Globale de la Plateforme KamerStock');

        // 1. Appel du Health Check
        $this->section("Etape 1 : Diagnostic Technique & Sante Systemique");
        $healthExitCode = $this->call('platform:health-check');
        $this->newLine();

        if ($healthExitCode !== Command::SUCCESS) {
            $this->error("[ERREUR CRITIQUE] L'etape 1 de diagnostic technique a signale des problemes.");
            $this->warn("Interruption de la verification globale.");
            return Command::FAILURE;
        }

        // 2. Appel du Check Subscriptions
        $this->section("Etape 2 : Verification de l'Etat des Abonnements");
        $this->call('platform:check-subscriptions');
        $this->newLine();

        // 3. Appel de platform:stats
        $this->section("Etape 3 : Calcul des Statistiques Generales");
        $this->call('platform:stats');
        $this->newLine();

        $this->info("Verification globale terminee avec succes. Tous les rapports ont ete generes.");
        return Command::SUCCESS;
    }

    /**
     * Affiche un titre stylise.
     */
    private function title(string $text)
    {
        $len = strlen($text);
        $line = str_repeat('*', $len + 4);
        $this->info($line);
        $this->info("* $text *");
        $this->info($line);
        $this->newLine();
    }

    /**
     * Affiche un titre de section.
     */
    private function section(string $text)
    {
        $this->warn(">>> $text <<<");
        $this->newLine();
    }
}
