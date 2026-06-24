<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Platform\PlatformStatsService;

class PlatformStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Affiche un rapport textuel des statistiques d\'usage et financières de la plateforme multi-tenant.';

    protected PlatformStatsService $statsService;

    /**
     * Create a new command instance.
     */
    public function __construct(PlatformStatsService $statsService)
    {
        parent::__construct();
        $this->statsService = $statsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->title('Rapport de Statistiques Consolidees - KamerStock');

        $this->info("Calcul des metriques de la plateforme...");
        $this->newLine();

        $overview = $this->statsService->overview();
        $tenantStats = $this->statsService->tenantStats();
        $subscriptionStats = $this->statsService->subscriptionStats();
        $paymentStats = $this->statsService->paymentStats();
        $backupStats = $this->statsService->backupStats();
        $healthSummary = $this->statsService->healthSummary();

        // 1. Boutiques (Tenants)
        $this->section('1. Statut des Boutiques (Tenants)');
        $this->table(
            ['Statut / Indicateur', 'Valeur'],
            [
                ['Nombre total de boutiques', $tenantStats['total']],
                ['Boutiques actives (En ligne)', $overview['active_tenants_count']],
                ['Boutiques en periode d\'essai', $overview['trial_tenants_count']],
                ['Boutiques suspendues', $overview['suspended_tenants_count']],
                ['Boutiques en lecture seule', $overview['read_only_tenants_count']],
                ['Creees ce mois', $tenantStats['created_this_month']],
                ['Creees aujourd\'hui', $tenantStats['created_today']],
            ]
        );

        // 2. Abonnements (Subscriptions)
        $this->section('2. Abonnements');
        $this->table(
            ['Statut Abonnement', 'Nombre'],
            [
                ['Actifs', $subscriptionStats['active']],
                ['En essai (Trial)', $subscriptionStats['trial']],
                ['Expirant sous 5 jours', $subscriptionStats['expiring_5_days']],
                ['Expires', $subscriptionStats['expired']],
                ['Suspendus', $subscriptionStats['suspended']],
                ['Annules', $subscriptionStats['cancelled']],
            ]
        );

        // 3. Finances (Payments)
        $this->section('3. Finances & Paiements');
        $this->table(
            ['Indicateur Financier', 'Valeur (FCFA) / Nombre'],
            [
                ['Total encaisse', number_format($paymentStats['total_amount'], 0, ',', ' ') . ' FCFA'],
                ['Encaisse ce mois', number_format($paymentStats['amount_this_month'], 0, ',', ' ') . ' FCFA'],
                ['Encaisse aujourd\'hui', number_format($paymentStats['amount_today'], 0, ',', ' ') . ' FCFA'],
                ['Transactions payees', $paymentStats['paid_count']],
                ['Transactions en attente', $paymentStats['pending_count']],
                ['Transactions echouees', $paymentStats['failed_count']],
            ]
        );

        // 4. Sauvegardes (Backups)
        $this->section('4. Sauvegardes de Bases de Donnees');
        $this->table(
            ['Indicateur Sauvegardes', 'Valeur'],
            [
                ['Sauvegardes reussies (Completed)', $backupStats['completed']],
                ['Sauvegardes echouees (Failed)', $backupStats['failed']],
                ['Sauvegardes en attente (Pending)', $backupStats['pending']],
                ['Sauvegardes en cours (Running)', $backupStats['running']],
                ['Boutiques sans sauvegarde', $backupStats['tenants_without_backup']],
                ['Derniere sauvegarde', $backupStats['last_backup'] ? $backupStats['last_backup']->finished_at->format('d/m/Y H:i:s') . ' (' . $backupStats['last_backup']->tenant?->name . ')' : 'Aucune'],
            ]
        );

        // 5. Etat de sante systeme
        $this->section('5. Etat de Sante Systemique');
        $this->table(
            ['Composant', 'Statut'],
            [
                ['Connexion Landlord DB', $healthSummary['landlord_connection_ok'] ? 'OK (Operationnelle)' : 'Echouee'],
                ['Tables Centrales (SaaS)', $healthSummary['platform_tables_ok'] ? 'OK (4/4)' : 'Incompletes'],
                ['Boutique Legacy Active', $healthSummary['legacy_tenant_exists'] ? 'OK (Enregistree)' : 'Absente'],
                ['Boutiques en preparation', $healthSummary['tenants_prepared_count']],
                ['Echecs de provisionnement', $healthSummary['tenants_failed_count'] > 0 ? $healthSummary['tenants_failed_count'] . ' (A VERIFIER)' : '0'],
                ['Echecs de sauvegardes', $healthSummary['failed_backups_count'] > 0 ? $healthSummary['failed_backups_count'] . ' (A VERIFIER)' : '0'],
                ['Sessions support actives', $healthSummary['active_support_count']],
            ]
        );

        $this->info("Rapport de statistiques genere avec succes.");
        return Command::SUCCESS;
    }

    /**
     * Affiche un titre stylise.
     */
    private function title(string $text)
    {
        $len = strlen($text);
        $line = str_repeat('=', $len + 4);
        $this->info($line);
        $this->info("| $text |");
        $this->info($line);
        $this->newLine();
    }

    /**
     * Affiche un titre de section.
     */
    private function section(string $text)
    {
        $this->warn("--- $text ---");
        $this->newLine();
    }
}
