@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 leading-none">Analyses & Statistiques Plateforme</h1>
            <p class="text-xs text-slate-400 font-medium mt-1">Données consolidées d'usage et métriques d'abonnement SaaS</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-200 rounded-full text-xs font-bold text-indigo-600 shadow-sm">
                Rapports temps réel
            </span>
        </div>
    </div>

    {{-- Disclaimer --}}
    <div class="mb-6 p-4 bg-slate-900 border border-slate-800 text-slate-400 rounded-xl text-xs flex items-center justify-between shadow-sm select-none">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span><strong>Confidentialité des données</strong> : Les statistiques affichées concernent uniquement les métadonnées plateforme. Les données métier des boutiques (ventes, clients, produits, fournisseurs) ne sont pas affichées ici.</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        {{-- Growth MoM --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <svg class="w-4.5 h-4.5 text-indigo-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Croissance des Boutiques (6 derniers mois)
            </h3>
            
            <div class="space-y-4">
                @forelse($growthStats as $month => $count)
                    <div>
                        <div class="flex justify-between text-xs font-semibold mb-1 text-slate-700">
                            <span>{{ $month }}</span>
                            <span>{{ $count }} inscription(s)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            @php
                                $maxGrowth = max(array_values($growthStats)) ?: 1;
                                $percent = ($count / $maxGrowth) * 100;
                            @endphp
                            <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Aucune donnée historique de croissance.</p>
                @endforelse
            </div>
        </div>

        {{-- Platform Health --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <svg class="w-4.5 h-4.5 text-emerald-550" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Santé Systémique Plateforme
            </h3>
            
            <ul class="space-y-3.5 text-xs">
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Connexion Landlord DB</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $healthSummary['landlord_connection_ok'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                        {{ $healthSummary['landlord_connection_ok'] ? 'Opérationnelle' : 'Échouée' }}
                    </span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Tables Centrales (SaaS)</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $healthSummary['platform_tables_ok'] ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $healthSummary['platform_tables_ok'] ? 'OK (4/4)' : 'Incomplètes' }}
                    </span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Boutique Legacy Active</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $healthSummary['legacy_tenant_exists'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $healthSummary['legacy_tenant_exists'] ? 'Enregistrée' : 'Absente' }}
                    </span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Boutiques en Préparation</span>
                    <span class="font-bold text-slate-800">{{ $healthSummary['tenants_prepared_count'] }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Échecs Provisionnement</span>
                    <span class="font-bold {{ $healthSummary['tenants_failed_count'] > 0 ? 'text-red-600 font-extrabold' : 'text-slate-800' }}">
                        {{ $healthSummary['tenants_failed_count'] }}
                    </span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Échecs Sauvegardes</span>
                    <span class="font-bold {{ $healthSummary['failed_backups_count'] > 0 ? 'text-rose-600 font-extrabold' : 'text-slate-800' }}">
                        {{ $healthSummary['failed_backups_count'] }}
                    </span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-400">Sessions Support Actives</span>
                    <span class="font-bold text-indigo-650">{{ $healthSummary['active_support_count'] }}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        {{-- Business Types --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <svg class="w-4.5 h-4.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Répartition par Type de Commerce
            </h3>
            
            <div class="space-y-3.5">
                @forelse($businessTypeStats as $type => $count)
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-semibold text-slate-700 capitalize">{{ str_replace('_', ' ', $type ?: 'Autre') }}</span>
                        <div class="flex items-center gap-3 w-2/3">
                            <div class="flex-1 bg-slate-100 h-2 rounded-full overflow-hidden">
                                @php
                                    $maxType = max(array_values($businessTypeStats)) ?: 1;
                                    $percent = ($count / $maxType) * 100;
                                @endphp
                                <div class="bg-amber-500 h-full rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="font-bold text-slate-900 w-8 text-right">{{ $count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Aucune répartition de commerce enregistrée.</p>
                @endforelse
            </div>
        </div>

        {{-- Plan distribution --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <svg class="w-4.5 h-4.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Répartition des Abonnements Actifs par Plan
            </h3>
            
            <div class="space-y-3.5">
                @forelse($planDistribution as $planName => $count)
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-semibold text-slate-700">{{ $planName }}</span>
                        <div class="flex items-center gap-3 w-2/3">
                            <div class="flex-1 bg-slate-100 h-2 rounded-full overflow-hidden">
                                @php
                                    $maxPlan = max(array_values($planDistribution)) ?: 1;
                                    $percent = ($count / $maxPlan) * 100;
                                @endphp
                                <div class="bg-blue-600 h-full rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="font-bold text-slate-900 w-8 text-right">{{ $count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Aucun abonnement actif trouvé.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Detail tables --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        {{-- Tenant states --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Statuts des Boutiques</h3>
            <ul class="space-y-3.5 text-xs">
                @foreach($tenantStats['by_status'] as $status => $count)
                    <li class="flex justify-between items-center">
                        <span class="capitalize text-slate-650">{{ config("platform.tenant_statuses.{$status}", $status) }}</span>
                        <span class="font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded">{{ $count }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Subscriptions states --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Statuts Abonnements</h3>
            <ul class="space-y-3.5 text-xs">
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Actifs</span>
                    <span class="font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded">{{ $subscriptionStats['active'] }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">En essai</span>
                    <span class="font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded">{{ $subscriptionStats['trial'] }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Expirant sous 5 jours</span>
                    <span class="font-bold text-amber-705 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded">{{ $subscriptionStats['expiring_5_days'] }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Expirés</span>
                    <span class="font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded">{{ $subscriptionStats['expired'] }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Suspendus</span>
                    <span class="font-bold text-red-700 bg-red-50 border border-red-100 px-2 py-0.5 rounded">{{ $subscriptionStats['suspended'] }}</span>
                </li>
            </ul>
        </div>

        {{-- Payments states --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Finances Plateforme</h3>
            <ul class="space-y-3.5 text-xs">
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Revenu global encaissé</span>
                    <span class="font-bold text-slate-900 font-mono">{{ number_format($paymentStats['total_amount'], 0, ',', ' ') }} FCFA</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Encaissé ce mois</span>
                    <span class="font-bold text-indigo-650 font-mono bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">{{ number_format($paymentStats['amount_this_month'], 0, ',', ' ') }} FCFA</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Encaissé aujourd'hui</span>
                    <span class="font-bold text-slate-900 font-mono">{{ number_format($paymentStats['amount_today'], 0, ',', ' ') }} FCFA</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Transactions payées</span>
                    <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">{{ $paymentStats['paid_count'] }}</span>
                </li>
                <li class="flex justify-between items-center">
                    <span class="text-slate-650">Transactions en attente</span>
                    <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">{{ $paymentStats['pending_count'] }}</span>
                </li>
            </ul>
        </div>

    </div>

</div>
@endsection
