@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium">Boutiques / Détails</p>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $tenant->name }}</h1>
        </div>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <a href="{{ route('landlord.tenants.edit', $tenant) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 rounded-xl transition select-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Modifier
            </a>
            @if($tenant->status !== 'suspended')
                <form action="{{ route('landlord.tenants.suspend', $tenant) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmer la suspension de cette boutique ?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-xs font-bold text-white rounded-xl shadow-md shadow-red-650/10 transition">
                        Suspendre la Boutique
                    </button>
                </form>
            @else
                <form action="{{ route('landlord.tenants.activate', $tenant) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white rounded-xl shadow-md shadow-emerald-650/10 transition">
                        Activer la Boutique
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Metadata Card --}}
        <div class="space-y-6">
            
            {{-- General Info Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Détails Système</h3>

                <ul class="space-y-3.5 text-xs">
                    <li class="flex justify-between items-start gap-4">
                        <span class="text-slate-400">UUID</span>
                        <span class="font-mono text-slate-700 text-right select-all">{{ $tenant->uuid }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Slug</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->slug }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Secteur</span>
                        <span class="font-semibold text-slate-800 capitalize">
                            @if($tenant->business_type === 'autre')
                                {{ $tenant->business_type_custom }}
                            @else
                                {{ str_replace('_', ' ', $tenant->business_type ?? 'N/A') }}
                            @endif
                        </span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Statut</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                            @if($tenant->status === 'active') bg-emerald-50 text-emerald-700
                            @elseif($tenant->status === 'trial') bg-indigo-50 text-indigo-750
                            @elseif($tenant->status === 'suspended') bg-red-50 text-red-700
                            @elseif($tenant->status === 'read_only') bg-amber-50 text-amber-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ $tenant->status }}
                        </span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Devise / Timezone</span>
                        <span class="font-semibold text-slate-850">{{ $tenant->currency }} / {{ $tenant->timezone }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Créée le</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Dernière connexion</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->last_login_at ? $tenant->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</span>
                    </li>
                </ul>
            </div>

            {{-- Contact Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Propriétaire de l'instance</h3>

                <ul class="space-y-3.5 text-xs">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Nom complet</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->owner_name ?? 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Adresse e-mail</span>
                        <span class="font-semibold text-slate-800 select-all">{{ $tenant->owner_email ?? 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Téléphone</span>
                        <span class="font-semibold text-slate-800 select-all">{{ $tenant->owner_phone ?? 'N/A' }}</span>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Right: Subscriptions, Payments, Backups & Audit Logs --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Subscriptions --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Historique d'abonnements</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Plan</th>
                                <th class="py-2">Période</th>
                                <th class="py-2">Montant</th>
                                <th class="py-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->subscriptions as $sub)
                                <tr>
                                    <td class="py-3 font-semibold text-slate-850">{{ $sub->plan?->name ?? 'N/A' }}</td>
                                    <td class="py-3">
                                        Du {{ $sub->starts_at?->format('d/m/Y') }} au {{ $sub->ends_at?->format('d/m/Y') }}
                                        @if($sub->trial_ends_at)
                                            <span class="block text-[10px] text-indigo-650">Essai gratuit jusqu'au {{ $sub->trial_ends_at->format('d/m/Y') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-semibold text-slate-900">{{ number_format($sub->amount, 0, ',', ' ') }} {{ $sub->currency }}</td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                            @if($sub->status === 'active') bg-emerald-50 text-emerald-700
                                            @elseif($sub->status === 'trial') bg-indigo-50 text-indigo-750
                                            @else bg-red-50 text-red-700 @endif">
                                            {{ $sub->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucun abonnement trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payments --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Transactions de paiement</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Date / Référence</th>
                                <th class="py-2">Méthode</th>
                                <th class="py-2">Montant</th>
                                <th class="py-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->subscriptionPayments as $pay)
                                <tr>
                                    <td class="py-3">
                                        <p class="font-semibold text-slate-800">{{ $pay->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $pay->reference ?? 'N/A' }}</p>
                                    </td>
                                    <td class="py-3 text-slate-650 capitalize">{{ $pay->payment_method ?? 'N/A' }}</td>
                                    <td class="py-3 font-semibold text-slate-900">{{ number_format($pay->amount, 0, ',', ' ') }} {{ $pay->currency }}</td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                            @if($pay->status === 'paid') bg-emerald-50 text-emerald-700
                                            @elseif($pay->status === 'pending') bg-amber-50 text-amber-705
                                            @else bg-red-50 text-red-700 @endif">
                                            {{ $pay->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucun paiement enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Backups --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Historique des sauvegardes</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Fichier</th>
                                <th class="py-2">Taille</th>
                                <th class="py-2">Date de création</th>
                                <th class="py-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->backups as $backup)
                                <tr>
                                    <td class="py-3 font-mono text-[10px] text-slate-700">{{ $backup->filename }}</td>
                                    <td class="py-3 font-semibold text-slate-800">{{ number_format($backup->size_bytes / 1024 / 1024, 2) }} Mo</td>
                                    <td class="py-3 text-slate-650">{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                            {{ $backup->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucune sauvegarde trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Audit Logs --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Actions Super Admin (Audit)</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Action</th>
                                <th class="py-2">Description</th>
                                <th class="py-2">Exécuté par</th>
                                <th class="py-2 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->auditLogs as $log)
                                <tr>
                                    <td class="py-3 font-semibold text-indigo-700">{{ $log->action }}</td>
                                    <td class="py-3 text-slate-650">{{ $log->description }}</td>
                                    <td class="py-3 text-slate-700 font-medium">{{ $log->landlordUser?->name ?? 'N/A' }}</td>
                                    <td class="py-3 text-right text-slate-450">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucun log d'audit pour cette boutique.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
