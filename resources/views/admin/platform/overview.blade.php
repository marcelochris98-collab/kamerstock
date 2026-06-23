@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium">Administration / Plateforme</p>
            <h1 class="text-xl font-bold text-slate-900 mt-1">Super Admin - Fondation SaaS</h1>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-200 rounded-full text-xs font-bold text-indigo-600 shadow-sm">
                <span class="w-2 h-2 bg-indigo-600 rounded-full animate-ping"></span>
                Fondation SaaS installée
            </span>
        </div>
    </div>

    {{-- System Status Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 text-white rounded-2xl p-6 shadow-md mb-6 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none transform translate-y-1/4 translate-x-1/10">
            <svg class="w-96 h-96" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2z"/>
            </svg>
        </div>
        <div class="relative z-10">
            <h2 class="text-lg font-bold">Bienvenue dans l'interface plateforme</h2>
            <p class="text-xs text-slate-350 max-w-xl mt-1.5">
                KamerStock est configuré en mode plateforme multi-boutique. Cette vue globale vous permet de suivre l'état de santé de toutes les instances boutiques, les souscriptions en cours et les transactions récentes.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <span class="px-2.5 py-1 bg-white/10 rounded-lg text-[10px] font-semibold text-slate-200 border border-white/10">
                    Base de données Landlord : <span class="text-indigo-300 font-mono">Platform</span>
                </span>
                <span class="px-2.5 py-1 bg-white/10 rounded-lg text-[10px] font-semibold text-slate-200 border border-white/10">
                    Stratégie : <span class="text-emerald-300">Base de données séparée (Future)</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        
        {{-- Tenants Count --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Boutiques (Tenants)</p>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-650 group-hover:scale-110 transition duration-150">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $tenantsCount }}</span>
                <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">Enregistrées</span>
            </div>
        </div>

        {{-- Plans Count --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Plans d'abonnement</p>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-650 group-hover:scale-110 transition duration-150">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $plansCount }}</span>
                <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-1.5 py-0.5 rounded">Actifs</span>
            </div>
        </div>

        {{-- Subscriptions Count --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Abonnements actifs</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-650 group-hover:scale-110 transition duration-150">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $subscriptionsCount }}</span>
                <span class="text-[10px] text-indigo-650 font-bold bg-indigo-50 px-1.5 py-0.5 rounded">Souscrits</span>
            </div>
        </div>

        {{-- Payments Count --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Transactions platform</p>
                <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-650 group-hover:scale-110 transition duration-150">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $paymentsCount }}</span>
                <span class="text-[10px] text-purple-600 font-bold bg-purple-50 px-1.5 py-0.5 rounded">Payées</span>
            </div>
        </div>

    </div>

    {{-- Content Details --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Tenants --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Dernières boutiques créées</h3>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px] font-semibold">Temps réel</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5">Boutique</th>
                            <th class="py-2.5">Secteur</th>
                            <th class="py-2.5">Statut</th>
                            <th class="py-2.5 text-right">Date création</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentTenants as $tenant)
                            <tr>
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded bg-slate-900 text-white font-black text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 leading-none">{{ $tenant->name }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5 leading-none">{{ $tenant->owner_email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-slate-650 capitalize">{{ str_replace('_', ' ', $tenant->business_type ?? 'N/A') }}</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                        @if($tenant->status === 'active') bg-emerald-50 text-emerald-700
                                        @elseif($tenant->status === 'trial') bg-indigo-50 text-indigo-700
                                        @else bg-red-50 text-red-700 @endif">
                                        {{ $tenant->status }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-500 text-right">{{ $tenant->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                    Aucune boutique enregistrée pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Derniers paiements d'abonnements</h3>
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px] font-semibold">Transactions</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2.5">Boutique</th>
                            <th class="py-2.5">Référence</th>
                            <th class="py-2.5">Montant</th>
                            <th class="py-2.5 text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentPayments as $payment)
                            <tr>
                                <td class="py-3">
                                    <p class="font-bold text-slate-800 leading-none">{{ $payment->tenant?->name ?? 'Boutique Inconnue' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5 leading-none">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="py-3 font-mono text-[10px] text-slate-600">{{ $payment->reference ?? 'N/A' }}</td>
                                <td class="py-3 font-bold text-slate-900">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                                <td class="py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                        @if($payment->status === 'paid') bg-emerald-50 text-emerald-700
                                        @elseif($payment->status === 'pending') bg-amber-50 text-amber-700
                                        @else bg-red-50 text-red-700 @endif">
                                        {{ $payment->status === 'paid' ? 'Payé' : ($payment->status === 'pending' ? 'En attente' : 'Échoué') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 text-xs">
                                    Aucune transaction enregistrée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
