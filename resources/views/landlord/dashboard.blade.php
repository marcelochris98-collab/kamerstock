@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900 leading-none">Console Super Admin</h1>
            <p class="text-xs text-slate-400 font-medium mt-1">Vue d'ensemble de la plateforme KamerStock</p>
        </div>
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-200 rounded-full text-xs font-bold text-indigo-600 shadow-sm">
                <span class="w-2 h-2 bg-indigo-600 rounded-full animate-ping"></span>
                Mode Plateforme Actif
            </span>
        </div>
    </div>

    {{-- System Status Banner --}}
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-2xl p-6 shadow-md mb-6 relative overflow-hidden">
        <div class="absolute right-0 bottom-0 opacity-10 pointer-events-none transform translate-y-1/4 translate-x-1/10">
            <svg class="w-96 h-96" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2z"/>
            </svg>
        </div>
        <div class="relative z-10">
            <h2 class="text-lg font-bold">Sécurité & Confidentialité des Boutiques</h2>
            <p class="text-xs text-indigo-200/80 max-w-xl mt-1.5 leading-relaxed">
                Les données métier des boutiques ne sont pas affichées ici. L’accès support aux espaces boutiques sera contrôlé et journalisé. En tant que propriétaire de la plateforme, vous gérez uniquement l'infrastructure, la facturation et les accès.
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        
        {{-- Total Tenants --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Boutiques totales</p>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $tenantsCount }}</span>
                <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-1.5 py-0.5 rounded">Enregistrées</span>
            </div>
        </div>

        {{-- Active Tenants --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Boutiques actives</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $activeTenantsCount }}</span>
                <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded">En ligne</span>
            </div>
        </div>

        {{-- Suspended Tenants --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Boutiques suspendues</p>
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-650 group-hover:scale-110 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $suspendedTenantsCount }}</span>
                <span class="text-[10px] text-red-650 font-bold bg-red-50 px-1.5 py-0.5 rounded">Hors ligne</span>
            </div>
        </div>

        {{-- Plans Count --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 group">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Plans Actifs</p>
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 group-hover:scale-110 transition duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900">{{ $plansCount }}</span>
                <span class="text-[10px] text-amber-700 font-bold bg-amber-50 px-1.5 py-0.5 rounded">SaaS</span>
            </div>
        </div>

    </div>

    {{-- Stats Cards 2 --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
        
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-650 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Abonnements expirant bientôt</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $expiringSubscriptionsCount }} <span class="text-[10px] text-slate-400 font-semibold">(Sous 7j)</span></p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-650 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Paiements en attente</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $pendingPaymentsCount }}</p>
            </div>
        </div>

        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-650 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Accès support actifs</p>
                <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $activeSupportCount }}</p>
            </div>
        </div>

    </div>

    {{-- Details Sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Tenants --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Dernières boutiques créées</h3>
                <a href="{{ route('landlord.tenants.index') }}" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800">Voir tout</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2">Boutique</th>
                            <th class="py-2">Propriétaire</th>
                            <th class="py-2 text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentTenants as $tenant)
                            <tr>
                                <td class="py-3 font-semibold text-slate-800">
                                    {{ $tenant->name }}
                                    <span class="block text-[10px] text-slate-400 font-mono font-normal">{{ $tenant->slug }}</span>
                                </td>
                                <td class="py-3">
                                    <p class="font-medium text-slate-650">{{ $tenant->owner_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $tenant->owner_email }}</p>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                        @if($tenant->status === 'active') bg-emerald-50 text-emerald-700
                                        @elseif($tenant->status === 'trial') bg-indigo-50 text-indigo-750
                                        @else bg-red-50 text-red-700 @endif">
                                        {{ $tenant->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-400 text-xs">Aucune boutique enregistrée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Backups --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Sauvegardes de bases de données récentes</h3>
                <a href="{{ route('landlord.backups.index') }}" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800">Voir tout</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-2">Boutique</th>
                            <th class="py-2">Fichier / Taille</th>
                            <th class="py-2 text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($recentBackups as $backup)
                            <tr>
                                <td class="py-3 font-semibold text-slate-800">{{ $backup->tenant?->name }}</td>
                                <td class="py-3">
                                    <p class="font-mono text-[10px] text-slate-650">{{ $backup->filename }}</p>
                                    <p class="text-[10px] text-slate-400 font-semibold">{{ number_format($backup->size_bytes / 1024 / 1024, 2) }} Mo</p>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                        {{ $backup->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 text-center text-slate-400 text-xs">Aucune sauvegarde trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
