<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Services\Tenancy\TenantContext;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\SubscriptionPayment;
use App\Services\Platform\LandlordAuditService;

class TenantBillingController extends Controller
{
    protected TenantContext $tenantContext;

    public function __construct(TenantContext $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Affiche l'espace facturation, le statut de l'essai et la grille des plans en libre-service.
     */
    public function index(Request $request): View
    {
        $tenant = $this->resolveTenant($request);

        // Récupérer l'abonnement actif / en cours
        $subscription = null;
        if ($tenant) {
            $subscription = Subscription::on('landlord')
                ->with('plan')
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', ['trial', 'active', 'grace_period', 'payment_due'])
                ->latest()
                ->first();
        }

        // Récupérer les plans publics actifs
        $plans = Plan::on('landlord')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Historique des paiements
        $payments = [];
        if ($tenant) {
            $payments = SubscriptionPayment::on('landlord')
                ->where('tenant_id', $tenant->id)
                ->latest()
                ->take(10)
                ->get();
        }

        // Calcul des jours d'essai restants
        $daysRemaining = 0;
        $isTrial = false;
        if ($tenant) {
            $isTrial = $tenant->status === 'trial';
            $trialEndDate = $tenant->trial_ends_at 
                ?? $subscription?->trial_ends_at 
                ?? now()->addDays(14);
            $daysRemaining = max(1, (int) ceil(now()->diffInSeconds($trialEndDate, false) / 86400));
        }

        return view('tenant.billing', compact(
            'tenant',
            'subscription',
            'plans',
            'payments',
            'daysRemaining',
            'isTrial'
        ));
    }

    /**
     * Traite la souscription ou le renouvellement autonome d'un plan.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        if (!$tenant) {
            return back()->with('error', 'Impossible d’identifier votre boutique pour la facturation.');
        }

        $validated = $request->validate([
            'plan_id'        => ['required', 'exists:landlord.platform_plans,id'],
            'billing_cycle'  => ['required', 'in:monthly,yearly'],
            'payment_method' => ['required', 'in:momo,orange_money,card,bank_transfer'],
            'phone_number'   => ['nullable', 'string', 'max:20'],
        ], [
            'plan_id.required'        => 'Veuillez sélectionner un plan d’abonnement.',
            'billing_cycle.required'  => 'Veuillez choisir un cycle de facturation (mensuel ou annuel).',
            'payment_method.required' => 'Veuillez sélectionner un moyen de paiement.',
        ]);

        $plan = Plan::on('landlord')->findOrFail($validated['plan_id']);

        $isYearly = $validated['billing_cycle'] === 'yearly';
        $amount = $isYearly ? $plan->price_yearly : $plan->price_monthly;
        $periodStart = now();
        $periodEnd = $isYearly ? now()->addYear() : now()->addMonth();

        $reference = 'PAY-' . strtoupper(Str::random(10));

        // 1. Enregistrer le paiement
        $payment = SubscriptionPayment::on('landlord')->create([
            'tenant_id'          => $tenant->id,
            'amount'             => $amount,
            'currency'           => $plan->currency ?? 'FCFA',
            'payment_method'     => $validated['payment_method'],
            'reference'          => $reference,
            'external_reference' => $validated['phone_number'] ?? null,
            'status'             => 'paid',
            'paid_at'            => now(),
            'period_start'       => $periodStart,
            'period_end'         => $periodEnd,
            'notes'              => "Souscription libre-service : Plan {$plan->name} (" . ($isYearly ? 'Annuel' : 'Mensuel') . ")",
            'metadata'           => [
                'billing_cycle' => $validated['billing_cycle'],
                'phone'         => $validated['phone_number'] ?? null,
                'ip'            => $request->ip(),
            ],
            'created_by'         => auth()->id(),
        ]);

        // 2. Mettre à jour ou créer l'abonnement
        $activeSub = Subscription::on('landlord')
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->first();

        if ($activeSub) {
            $activeSub->update([
                'plan_id'       => $plan->id,
                'status'        => 'active',
                'billing_cycle' => $validated['billing_cycle'],
                'starts_at'     => $periodStart,
                'ends_at'       => $periodEnd,
                'price'         => $amount,
            ]);
            $payment->update(['subscription_id' => $activeSub->id]);
        } else {
            $newSub = Subscription::on('landlord')->create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'status'        => 'active',
                'billing_cycle' => $validated['billing_cycle'],
                'starts_at'     => $periodStart,
                'ends_at'       => $periodEnd,
                'price'         => $amount,
                'currency'      => $plan->currency ?? 'FCFA',
            ]);
            $payment->update(['subscription_id' => $newSub->id]);
        }

        // 3. Mettre à jour le statut du tenant
        $tenant->update([
            'status'               => 'active',
            'subscription_ends_at' => $periodEnd,
            'plan_id'              => $plan->id,
            'suspended_at'         => null,
            'read_only_at'         => null,
        ]);

        // 4. Journal d'audit Landlord
        try {
            LandlordAuditService::record(
                'tenant_subscription_upgraded',
                $tenant,
                "Abonnement activé en libre-service : Plan {$plan->name} ({$amount} FCFA) par " . auth()->user()->email
            );
        } catch (\Throwable $e) {
            // Ignorer si log indisponible
        }

        return redirect()->route('tenant.billing')
            ->with('success', "Félicitations ! Votre abonnement au Plan {$plan->name} est activé jusqu'au {$periodEnd->format('d/m/Y')}. Votre reçu #{$reference} a été émis.");
    }

    /**
     * Résout l'instance du tenant courant avec secours sur la session ou la base landlord.
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        if ($this->tenantContext->hasTenant()) {
            return $this->tenantContext->tenant();
        }

        $slug = $request->route('tenant')
            ?? $request->query('tenant')
            ?? session('current_tenant_slug');

        if ($slug) {
            $tenant = Tenant::on('landlord')->where('slug', $slug)->first();
            if ($tenant) {
                $this->tenantContext->setTenant($tenant);
                return $tenant;
            }
        }

        return Tenant::on('landlord')->first();
    }
}
