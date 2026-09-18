@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800 space-y-6">

    {{-- Top Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Console Super Admin</h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Vue d'ensemble et contrôle de la plateforme KamerStock</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full text-xs font-bold text-emerald-700 shadow-sm">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                Mode Plateforme Actif
            </span>
            <a href="{{ route('landlord.tenants.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                + Nouvelle Boutique
            </a>
            <a href="{{ route('landlord.support.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold shadow-sm transition">
                Console Support
            </a>
        </div>
    </div>

    {{-- Security Notice Banner (Solid Dark Slate background for max readability) --}}
    <div class="bg-slate-900 text-white rounded-2xl p-5 shadow-lg border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-400/30 flex items-center justify-center text-indigo-300 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-white">Confidentialité des Boutiques & Isolation Multi-Tenant</h2>
                <p class="text-xs text-slate-300 mt-0.5 leading-relaxed">
                    Les données métier des boutiques restent strictly confidentielles et isolées. Seule l'infrastructure globale et la facturation sont gérées ici.
                </p>
            </div>
        </div>
        <div class="flex-shrink-0">
            <span class="inline-block px-3 py-1 bg-indigo-950 border border-indigo-700/50 text-indigo-300 text-[10px] font-mono font-bold rounded-lg uppercase tracking-wider">
                Tenant Guard Active
            </span>
        </div>
    </div>

    {{-- Hero 4 KPI Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        {{-- Card 1: Boutiques --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Boutiques Total</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $tenantsCount }}</span>
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1 text-xs font-bold bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-200">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                        {{ $activeTenantsCount }} Actives
                    </span>
                    @if($suspendedTenantsCount > 0)
                        <span class="inline-flex items-center text-xs font-bold bg-rose-50 text-rose-700 px-2 py-0.5 rounded border border-rose-200">
                            {{ $suspendedTenantsCount }} HS
                        </span>
                    @endif
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Boutiques enregistrées en plateforme</p>
        </div>

        {{-- Card 2: Offres & Paiements --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Plans & Abonnements</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $plansCount }}</span>
                    <span class="text-xs font-bold text-slate-400 ml-1">Offres</span>
                </div>
                @if($pendingPaymentsCount > 0)
                    <span class="inline-flex items-center gap-1 text-xs font-bold bg-amber-50 text-amber-700 px-2 py-0.5 rounded border border-amber-200">
                        {{ $pendingPaymentsCount }} en attente
                    </span>
                @else
                    <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                        0 en attente
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Plans SaaS actifs sur la plateforme</p>
        </div>

        {{-- Card 3: Alertes Abonnements --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Abonnements (-7j)</span>
                <div class="w-9 h-9 rounded-xl {{ $expiringSubscriptionsCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $expiringSubscriptionsCount }}</span>
                @if($expiringSubscriptionsCount > 0)
                    <span class="inline-flex items-center text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                        Attention requise
                    </span>
                @else
                    <span class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        À jour
                    </span>
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Renouvellement requis cette semaine</p>
        </div>

        {{-- Card 4: Sauvegardes DB --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 hover:shadow-md transition duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Sauvegardes DB</span>
                <div class="w-9 h-9 rounded-xl {{ $failedBackupsCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $completedBackupsCount }}</span>
                    <span class="text-xs font-bold text-emerald-600 ml-1">OK</span>
                </div>
                <div class="flex items-center gap-1">
                    @if($failedBackupsCount > 0)
                        <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                            {{ $failedBackupsCount }} Échecs
                        </span>
                    @endif
                    @if($tenantsWithoutBackupCount > 0)
                        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                            {{ $tenantsWithoutBackupCount }} sans backup
                        </span>
                    @endif
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 font-medium">Bases de données protégées</p>
        </div>

    </div>

    {{-- Main Grid: 3 Columns (Left 2 Cols, Right 1 Col) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Section (2 Cols) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Recent Tenants Table Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Dernières Boutiques Inscrites</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Nouveaux espaces marchands sur la plateforme</p>
                    </div>
                    <a href="{{ route('landlord.tenants.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Voir tout →
                    </a>
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
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 font-semibold text-slate-900">
                                        {{ $tenant->name }}
                                        <span class="block text-[10px] text-slate-400 font-mono font-normal">{{ $tenant->slug }}</span>
                                    </td>
                                    <td class="py-3">
                                        <p class="font-medium text-slate-700">{{ $tenant->owner_name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $tenant->owner_email }}</p>
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                                            @if($tenant->status === 'active') bg-emerald-50 text-emerald-700 border border-emerald-100
                                            @elseif($tenant->status === 'trial') bg-indigo-50 text-indigo-700 border border-indigo-100
                                            @else bg-red-50 text-red-700 border border-red-100 @endif">
                                            {{ ucfirst($tenant->status) }}
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

            {{-- Recent DB Backups Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Sauvegardes de Bases de Données Récentes</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Dernières sauvegardes automatiques exécutées</p>
                    </div>
                    <a href="{{ route('landlord.backups.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Voir tout →
                    </a>
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
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 font-semibold text-slate-900">{{ $backup->tenant?->name ?? 'Boutique' }}</td>
                                    <td class="py-3">
                                        <p class="font-mono text-[10px] text-slate-600 truncate max-w-[200px]">{{ $backup->filename }}</p>
                                        <p class="text-[10px] text-slate-400 font-semibold">{{ number_format($backup->size_bytes / 1024 / 1024, 2) }} Mo</p>
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            {{ ucfirst($backup->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400 text-xs">Aucune sauvegarde enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Right Section (1 Col) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Support & Maintenance Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Console Support</h3>
                        <p class="text-xs text-slate-400">Interventions temporaires</p>
                    </div>
                    <a href="{{ route('landlord.support.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Tout voir →
                    </a>
                </div>

                {{-- Sessions Actives --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Sessions Actives</span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                            {{ $activeSupportAccesses->count() }}
                        </span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($activeSupportAccesses as $access)
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-2">
                                <div class="flex justify-between items-start gap-2">
                                    <span class="font-bold text-slate-900 truncate">{{ $access->tenant?->name ?? 'Boutique' }}</span>
                                    <span class="text-[9px] font-mono font-bold bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded border border-emerald-200">
                                        ACTIF
                                    </span>
                                </div>
                                <p class="text-slate-500 text-[11px] truncate">Motif: {{ $access->reason }}</p>
                                <div class="flex items-center justify-between text-[10px] text-slate-500">
                                    <span>Temps restant :</span>
                                    <span class="font-mono font-bold text-indigo-700">{{ $access->remainingDurationLabel() }}</span>
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <a href="{{ route('landlord.support.enter', $access) }}" class="flex-1 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-center transition text-[11px]">
                                        Entrer
                                    </a>
                                    <form action="{{ route('landlord.support.revoke', $access) }}" method="POST" onsubmit="return confirm('Révoquer cet accès support ?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 rounded-lg transition text-[11px] font-bold">
                                            Révoquer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-slate-400 text-xs bg-slate-50 rounded-xl border border-slate-100">
                                Aucune session active
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Demandes en Attente --}}
                @if($pendingSupportAccesses->count() > 0)
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">En Attente</span>
                            <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                                {{ $pendingSupportAccesses->count() }}
                            </span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach($pendingSupportAccesses as $access)
                                <div class="p-3 bg-amber-50/60 border border-amber-200 rounded-xl text-xs space-y-2">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="font-bold text-slate-900 truncate">{{ $access->tenant?->name ?? 'Boutique' }}</span>
                                        <span class="text-[9px] font-mono font-bold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">ATTENTE</span>
                                    </div>
                                    <p class="text-slate-600 text-[11px] truncate">Motif: {{ $access->reason }}</p>
                                    <form action="{{ route('landlord.support.activate', $access) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-center transition text-[11px]">
                                            Activer l'accès
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-3 border-t border-slate-100">
                    <a href="{{ route('landlord.support.create') }}" class="block w-full text-center py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                        + Créer une demande support
                    </a>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection
