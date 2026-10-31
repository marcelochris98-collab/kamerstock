@extends('layouts.app')

@section('title', 'Abonnement & Facturation')
@section('page-title', 'Abonnement & Facturation')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="billingManager()">

    <!-- ========================================================================= -->
    <!-- 1. EN-TÊTE & STATUT ACTUEL -->
    <!-- ========================================================================= -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Abonnement & Facturation</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Gérez votre formule SaaS, vos cycles de facturation et profitez de la haute disponibilité de votre base dédiée.
            </p>
        </div>

        @if($isTrial)
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-500 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <span>Essai gratuit · <strong>{{ $daysRemaining }} jour{{ $daysRemaining > 1 ? 's' : '' }} restant{{ $daysRemaining > 1 ? 's' : '' }}</strong></span>
        </div>
        @else
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Abonnement actif · Valide jusqu'au {{ $tenant->subscription_ends_at?->format('d/m/Y') ?? 'N/A' }}</span>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-medium flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-medium flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. CARTE RÉCAPITULATIVE DE LA BOUTIQUE & DE L'ISOLATION -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/[0.08] rounded-2xl p-5 sm:p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 divide-y md:divide-y-0 md:divide-x divide-slate-100 dark:divide-white/[0.06]">
            
            <!-- Boutique & Identifiant -->
            <div class="space-y-1 md:pr-4">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Boutique</span>
                <p class="text-sm font-bold text-slate-800 dark:text-white truncate">{{ $tenant->name ?? 'Votre boutique' }}</p>
                <p class="text-xs text-amber-500 font-mono">kamerstock.cm/t/{{ $tenant->slug ?? 'boutique' }}</p>
            </div>

            <!-- Formule courante -->
            <div class="pt-3 md:pt-0 md:px-4 space-y-1">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Plan actuel</span>
                <p class="text-sm font-bold text-slate-800 dark:text-white">
                    {{ $subscription?->plan?->name ?? 'Plan Standard' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $isTrial ? 'Période d\'évaluation (14j)' : 'Abonnement commercial' }}
                </p>
            </div>

            <!-- Isolation Base de données -->
            <div class="pt-3 md:pt-0 md:px-4 space-y-1">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Architecture Données</span>
                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Base MySQL Isolée</span>
                </div>
                <p class="text-[11px] text-slate-500 font-mono truncate">DB: {{ $tenant->database_name ?? 'Dédiée' }}</p>
            </div>

            <!-- Échéance & Action -->
            <div class="pt-3 md:pt-0 md:pl-4 space-y-2 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Date d'échéance</span>
                    <p class="text-sm font-bold text-slate-800 dark:text-white">
                        {{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('d/m/Y') : ($tenant->trial_ends_at ? $tenant->trial_ends_at->format('d/m/Y') : '14 jours') }}
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. SÉLECTEUR DE FORFAIT EN LIBRE-SERVICE (MENSUEL / ANNUEL) -->
    <!-- ========================================================================= -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Choisir ou renouveler votre plan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Paiement 100% autonome par MTN MoMo, Orange Money ou Carte Bancaire.</p>
            </div>

            <!-- Toggle Mensuel / Annuel -->
            <div class="inline-flex items-center p-1 bg-slate-200 dark:bg-slate-800 rounded-xl text-xs font-semibold self-start sm:self-auto select-none">
                <button type="button" 
                        @click="billingCycle = 'monthly'" 
                        :class="billingCycle === 'monthly' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition">
                    Mensuel
                </button>
                <button type="button" 
                        @click="billingCycle = 'yearly'" 
                        :class="billingCycle === 'yearly' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <span>Annuel</span>
                    <span class="px-1.5 py-0.2 text-[9px] uppercase rounded bg-slate-950/20 text-slate-900 font-black">-15%</span>
                </button>
            </div>
        </div>

        <!-- Grille des plans -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @forelse($plans as $plan)
            @php
                $isCurrent = $subscription && $subscription->plan_id === $plan->id;
                $isPopular = strtolower($plan->slug) === 'standard' || strtolower($plan->name) === 'standard';
            @endphp
            <div class="relative bg-white dark:bg-slate-900/90 rounded-2xl border transition duration-200 flex flex-col justify-between p-5 sm:p-6 shadow-sm
                        {{ $isPopular ? 'border-amber-500 shadow-amber-500/10 dark:shadow-amber-500/5 ring-1 ring-amber-500' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-white/[0.15]' }}">
                
                @if($isPopular)
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider shadow-sm">
                    Recommandé
                </div>
                @endif

                <div>
                    <!-- En-tête plan -->
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $plan->name }}</h3>
                        @if($isCurrent)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                            Actuel
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400 min-h-[32px] mb-4">
                        {{ $plan->description ?? 'Solution complète pour la gestion commerciale et le point de vente.' }}
                    </p>

                    <!-- Prix dynamique selon cycle -->
                    <div class="mb-5 pb-5 border-b border-slate-100 dark:border-white/[0.06]">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white"
                                  x-text="billingCycle === 'monthly' ? '{{ number_format($plan->price_monthly, 0, ',', ' ') }}' : '{{ number_format($plan->price_yearly, 0, ',', ' ') }}'">
                                {{ number_format($plan->price_monthly, 0, ',', ' ') }}
                            </span>
                            <span class="text-xs text-slate-500 font-medium">FCFA</span>
                            <span class="text-xs text-slate-400" x-text="billingCycle === 'monthly' ? '/mois' : '/an'">/mois</span>
                        </div>
                    </div>

                    <!-- Liste des fonctionnalités -->
                    <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 mb-6">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>{{ $plan->max_products ? number_format($plan->max_products, 0, ',', ' ') : 'Articles illimités' }}</strong> références</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>{{ $plan->max_users ?? '1' }}</strong> compte{{ ($plan->max_users ?? 1) > 1 ? 's' : '' }} caissier / gérant</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>{{ $plan->max_branches ?? '1' }}</strong> boutique{{ ($plan->max_branches ?? 1) > 1 ? 's' : '' }} connectée{{ ($plan->max_branches ?? 1) > 1 ? 's' : '' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Base de données MySQL dédiée & isolée</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Caisse POS & impression reçus 80mm</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Gestion des crédits & alertes WhatsApp</span>
                        </li>
                    </ul>
                </div>

                <!-- Bouton d'action -->
                <div>
                    <button type="button" 
                            @click="openCheckoutModal({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price_monthly }}, {{ $plan->price_yearly }})"
                            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5
                                   {{ $isPopular ? 'bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-900 hover:bg-slate-800 text-white dark:bg-white dark:hover:bg-slate-100 dark:text-slate-950' }}">
                        <span>{{ $isCurrent && !$isTrial ? 'Renouveler ce forfait' : 'Activer ce forfait' }} &rarr;</span>
                    </button>
                </div>

            </div>
            @empty
            <div class="col-span-3 p-8 text-center bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-white/[0.08]">
                <p class="text-sm text-slate-500">Aucun plan public n'est configuré pour le moment.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. HISTORIQUE DES FACTURES & REÇUS DE PAIEMENT -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/[0.08] rounded-2xl p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Historique des transactions & factures</h2>
            <span class="text-xs text-slate-400 font-mono">{{ count($payments) }} transaction{{ count($payments) > 1 ? 's' : '' }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/[0.06] text-slate-400 text-[11px] uppercase tracking-wider">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Référence</th>
                        <th class="py-2.5 px-3">Mode de paiement</th>
                        <th class="py-2.5 px-3">Montant</th>
                        <th class="py-2.5 px-3">Période couverte</th>
                        <th class="py-2.5 px-3 text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                    @forelse($payments as $payment)
                    <tr class="text-slate-700 dark:text-slate-300">
                        <td class="py-3 px-3 whitespace-nowrap">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-3 font-mono font-medium text-amber-500">{{ $payment->reference }}</td>
                        <td class="py-3 px-3 uppercase text-[10px] font-semibold">
                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $payment->payment_method ?? 'MoMo / OM' }}
                            </span>
                        </td>
                        <td class="py-3 px-3 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                            {{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency ?? 'FCFA' }}
                        </td>
                        <td class="py-3 px-3 text-[11px] text-slate-500">
                            {{ $payment->period_start?->format('d/m/Y') }} &rarr; {{ $payment->period_end?->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-3 text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                ✓ Payé
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                            Aucune facture émise pour le moment. Votre boutique utilise actuellement ses 14 jours d'essai gratuit.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. MODAL DE CHECKOUT SÉCURISÉ (MTN MoMo, Orange Money, Carte) -->
    <!-- ========================================================================= -->
    <div x-show="showCheckout" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         @keydown.escape.window="showCheckout = false">
        
        <div class="relative bg-slate-900 border border-white/[0.1] rounded-2xl max-w-md w-full p-6 text-white shadow-2xl space-y-4"
             @click.away="showCheckout = false">
            
            <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                <div>
                    <h3 class="text-base font-bold text-white">Souscription — <span x-text="selectedPlanName" class="text-amber-400"></span></h3>
                    <p class="text-xs text-slate-400">Paiement instantané en ligne</p>
                </div>
                <button type="button" @click="showCheckout = false" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('tenant.billing.checkout') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="plan_id" :value="selectedPlanId">
                <input type="hidden" name="billing_cycle" :value="billingCycle">

                <!-- Récapitulatif montant -->
                <div class="p-3 rounded-xl bg-slate-950/80 border border-white/[0.06] flex items-center justify-between">
                    <span class="text-xs text-slate-400">Montant à régler :</span>
                    <span class="text-lg font-black text-amber-400" x-text="formatPrice(computedPrice) + ' FCFA'"></span>
                </div>

                <!-- Choix moyen de paiement -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Sélectionnez votre moyen de paiement :</label>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="paymentMethod === 'momo' ? 'border-amber-500 bg-amber-500/10 text-amber-300 font-bold' : 'border-white/[0.08] bg-slate-950/60 text-slate-400 hover:border-white/[0.2]'">
                            <input type="radio" name="payment_method" value="momo" x-model="paymentMethod" class="sr-only">
                            <span class="text-sm font-black text-yellow-400 mb-0.5">MTN</span>
                            <span class="text-[10px]">MoMo</span>
                        </label>

                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="paymentMethod === 'orange_money' ? 'border-amber-500 bg-amber-500/10 text-amber-300 font-bold' : 'border-white/[0.08] bg-slate-950/60 text-slate-400 hover:border-white/[0.2]'">
                            <input type="radio" name="payment_method" value="orange_money" x-model="paymentMethod" class="sr-only">
                            <span class="text-sm font-black text-orange-400 mb-0.5">Orange</span>
                            <span class="text-[10px]">Money</span>
                        </label>

                        <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="paymentMethod === 'card' ? 'border-amber-500 bg-amber-500/10 text-amber-300 font-bold' : 'border-white/[0.08] bg-slate-950/60 text-slate-400 hover:border-white/[0.2]'">
                            <input type="radio" name="payment_method" value="card" x-model="paymentMethod" class="sr-only">
                            <span class="text-sm font-black text-indigo-400 mb-0.5">CB</span>
                            <span class="text-[10px]">Visa / MC</span>
                        </label>
                    </div>
                </div>

                <!-- Numéro de téléphone pour Mobile Money -->
                <div x-show="paymentMethod === 'momo' || paymentMethod === 'orange_money'">
                    <label for="phone_number" class="block text-xs font-semibold text-slate-300 mb-1">
                        Numéro de téléphone <span x-text="paymentMethod === 'momo' ? 'MTN MoMo' : 'Orange Money'"></span> :
                    </label>
                    <input type="text" 
                           id="phone_number" 
                           name="phone_number" 
                           placeholder="6XXXXXXXX"
                           class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 rounded-lg px-3 py-2 text-xs text-white placeholder-slate-500">
                    <p class="text-[10px] text-slate-500 mt-1">Vous recevrez un prompt de validation sur votre téléphone.</p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="showCheckout = false" class="px-4 py-2 text-xs text-slate-400 hover:text-white transition">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-md shadow-amber-500/20 transition">
                        Confirmer le règlement &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

<script>
    function billingManager() {
        return {
            billingCycle: 'monthly',
            showCheckout: false,
            selectedPlanId: null,
            selectedPlanName: '',
            selectedMonthlyPrice: 0,
            selectedYearlyPrice: 0,
            paymentMethod: 'momo',

            get computedPrice() {
                return this.billingCycle === 'monthly' ? this.selectedMonthlyPrice : this.selectedYearlyPrice;
            },

            openCheckoutModal(id, name, monthlyPrice, yearlyPrice) {
                this.selectedPlanId = id;
                this.selectedPlanName = name;
                this.selectedMonthlyPrice = monthlyPrice;
                this.selectedYearlyPrice = yearlyPrice;
                this.showCheckout = true;
            },

            formatPrice(val) {
                return new Intl.NumberFormat('fr-FR').format(val || 0);
            }
        }
    }
</script>
@endsection
