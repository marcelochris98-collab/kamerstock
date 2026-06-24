<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use Carbon\Carbon;

class CheckSubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:check-subscriptions {--status= : Filtrer par statut d\'abonnement (active, expired, trial, suspended, cancelled)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liste l\'état des abonnements de tous les tenants et met en évidence ceux arrivant à échéance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Verification des abonnements des boutiques...");
        $this->newLine();

        $statusFilter = $this->option('status');

        $query = Subscription::with(['tenant', 'plan']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->warn("Aucun abonnement trouve avec les criteres demandes.");
            return Command::SUCCESS;
        }

        $headers = ['Boutique (Slug)', 'Plan', 'Statut', 'Début', 'Échéance', 'Jours Restants', 'Auto-Renouvellement'];
        $rows = [];

        foreach ($subscriptions as $sub) {
            $tenantName = $sub->tenant ? $sub->tenant->name . ' (' . $sub->tenant->slug . ')' : 'Inconnu';
            $planName = $sub->plan ? $sub->plan->name : 'N/A';
            
            $daysLeft = 'Expiré';
            if ($sub->ends_at) {
                if ($sub->ends_at->isFuture()) {
                    $daysLeft = $sub->ends_at->diffInDays(Carbon::now()) . ' jour(s)';
                } else {
                    $daysLeft = 'Expiré il y a ' . $sub->ends_at->diffInDays(Carbon::now()) . ' jour(s)';
                }
            } elseif ($sub->trial_ends_at && $sub->status === 'trial') {
                if ($sub->trial_ends_at->isFuture()) {
                    $daysLeft = $sub->trial_ends_at->diffInDays(Carbon::now()) . ' jour(s) (Essai)';
                } else {
                    $daysLeft = 'Essai expiré';
                }
            }

            $rows[] = [
                $tenantName,
                $planName,
                strtoupper($sub->status),
                $sub->starts_at ? $sub->starts_at->format('d/m/Y') : 'N/A',
                $sub->ends_at ? $sub->ends_at->format('d/m/Y') : ($sub->trial_ends_at ? $sub->trial_ends_at->format('d/m/Y') . ' (Essai)' : 'Indéfini'),
                $daysLeft,
                $sub->auto_renew ? 'Oui' : 'Non',
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // Statistiques rapides
        $totalCount = $subscriptions->count();
        $this->info("Nombre total d'abonnements affiches : $totalCount");

        return Command::SUCCESS;
    }
}
