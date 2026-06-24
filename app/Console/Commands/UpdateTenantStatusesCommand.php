<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use App\Services\Platform\LandlordAuditService;
use Carbon\Carbon;

class UpdateTenantStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'platform:update-tenant-statuses {--dry-run : Executer sans modifier la base de donnees}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifie et met a jour le statut des boutiques (active, trial, read_only, suspended) en fonction des dates de fin d\'abonnement ou d\'essai.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("[MODE DRY-RUN] Aucune modification ne sera enregistree en base de donnees.");
        }

        $this->info("Verification de la validite des abonnements et essais des boutiques...");
        $this->newLine();

        $tenants = Tenant::all();
        $updatedCount = 0;

        foreach ($tenants as $tenant) {
            $currentStatus = $tenant->status;
            $newStatus = $currentStatus;

            // 1. Boutiques de type "legacy" ou preparees ne sont pas impactees
            if (in_array($tenant->provisioning_status, ['prepared', 'legacy_current_db']) && !$tenant->isProvisioned()) {
                // Si la boutique n'est pas encore provisionnee, on ne touche pas au statut
                continue;
            }

            // 2. Recherche d'un abonnement actif (qui n'a pas expire)
            $activeSubscription = Subscription::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->where('starts_at', '<=', Carbon::now())
                ->where('ends_at', '>', Carbon::now())
                ->first();

            // 3. Logique de determination du statut
            if ($activeSubscription) {
                // S'il y a un abonnement actif en cours, la boutique doit etre active
                if (!in_array($currentStatus, ['active'])) {
                    $newStatus = 'active';
                }
            } else {
                // Pas d'abonnement actif. Verifions la periode d'essai
                $trialActive = $tenant->trial_ends_at && $tenant->trial_ends_at->isFuture();

                if ($trialActive) {
                    if ($currentStatus !== 'trial') {
                        $newStatus = 'trial';
                    }
                } else {
                    // Essai depasse ET pas d'abonnement actif.
                    // Verifions si l'abonnement/essai vient tout juste d'expirer pour appliquer une grace period (ex: 3 jours en lecture seule avant suspension)
                    $expirationDate = $tenant->subscription_ends_at ?? $tenant->trial_ends_at;

                    if ($expirationDate) {
                        $daysExpired = $expirationDate->diffInDays(Carbon::now(), false);

                        if ($daysExpired > 0) {
                            if ($daysExpired <= 3) {
                                // Grace period de 3 jours : Lecture seule
                                if ($currentStatus !== 'read_only') {
                                    $newStatus = 'read_only';
                                }
                            } else {
                                // Suspension totale apres 3 jours
                                if ($currentStatus !== 'suspended') {
                                    $newStatus = 'suspended';
                                }
                            }
                        }
                    } else {
                        // Pas de date d'essai ni d'abonnement defini : boutique suspecte, par defaut suspendue si non legacy
                        if ($currentStatus !== 'suspended' && $tenant->provisioning_status !== 'legacy_current_db') {
                            $newStatus = 'suspended';
                        }
                    }
                }
            }

            // 4. Application du nouveau statut
            if ($newStatus !== $currentStatus) {
                $this->info("Boutique '{$tenant->name}' ({$tenant->slug}) : Statut {$currentStatus} -> {$newStatus}");

                if (!$dryRun) {
                    $tenant->status = $newStatus;
                    
                    if ($newStatus === 'suspended') {
                        $tenant->suspended_at = Carbon::now();
                    } elseif ($newStatus === 'read_only') {
                        $tenant->read_only_at = Carbon::now();
                        $tenant->suspended_at = null;
                    } else {
                        $tenant->suspended_at = null;
                        $tenant->read_only_at = null;
                    }

                    $tenant->save();

                    // Loguer l'evenement d'audit landlord
                    LandlordAuditService::record(
                        'tenant_status_updated_auto',
                        $tenant,
                        "Mise a jour automatique du statut de la boutique de {$currentStatus} vers {$newStatus} (Expiration abonnement/essai)."
                    );
                }
                $updatedCount++;
            }
        }

        $this->newLine();
        $this->info("Traitement termine. Nombre de boutiques mises a jour : $updatedCount");

        return Command::SUCCESS;
    }
}
