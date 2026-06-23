@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Boutiques (Tenants)</h1>
            <p class="text-xs text-slate-400 font-medium mt-1">Gestion de toutes les instances boutiques de KamerStock</p>
        </div>
        <div>
            <a href="{{ route('landlord.tenants.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-md shadow-indigo-650/10 transition select-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une Boutique
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Propriétaire</th>
                        <th class="px-6 py-4">Type / Activité</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4">Abonnement</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($tenants as $tenant)
                        @php
                            $status = app(\App\Services\Platform\TenantStatusService::class)->determineStatus($tenant);
                        @endphp
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white font-black text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 leading-none">{{ $tenant->name }}</p>
                                        <p class="text-[10px] text-slate-450 mt-1 leading-none font-mono">UUID: {{ substr($tenant->uuid, 0, 8) }}...</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700 leading-none">{{ $tenant->owner_name ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-450 mt-1 leading-none">{{ $tenant->owner_email }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-650 capitalize">
                                {{ str_replace('_', ' ', $tenant->business_type ?? 'N/A') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    @if($status === 'active') bg-emerald-50 text-emerald-700
                                    @elseif($status === 'trial') bg-indigo-50 text-indigo-750
                                    @elseif($status === 'suspended') bg-red-50 text-red-700
                                    @elseif($status === 'read_only') bg-amber-50 text-amber-700
                                    @else bg-slate-100 text-slate-600 @endif">
                                    {{ config("platform.tenant_statuses.{$status}", $status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($tenant->subscription_ends_at)
                                    <p class="font-semibold text-slate-750">Exp : {{ \Carbon\Carbon::parse($tenant->subscription_ends_at)->format('d/m/Y') }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Payant</p>
                                @elseif($tenant->trial_ends_at)
                                    <p class="font-semibold text-indigo-750">Essai : {{ \Carbon\Carbon::parse($tenant->trial_ends_at)->format('d/m/Y') }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Gratuit</p>
                                @else
                                    <p class="text-slate-400">Aucun</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('landlord.tenants.show', $tenant) }}" class="inline-flex items-center justify-center p-1 bg-slate-100 hover:bg-slate-200 text-slate-650 rounded-lg transition" title="Détails">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('landlord.tenants.edit', $tenant) }}" class="inline-flex items-center justify-center p-1 bg-slate-100 hover:bg-slate-200 text-indigo-600 rounded-lg transition" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </a>
                                
                                @if($tenant->status !== 'suspended')
                                    <form action="{{ route('landlord.tenants.suspend', $tenant) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmer la suspension de cette boutique ?')">
                                        @csrf
                                        <button type="submit" class="p-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition" title="Suspendre">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>
                                    </form>
                                    
                                    @if($tenant->status !== 'read_only')
                                        <form action="{{ route('landlord.tenants.read_only', $tenant) }}" method="POST" class="inline-block" onsubmit="return confirm('Passer cette boutique en lecture seule ?')">
                                            @csrf
                                            <button type="submit" class="p-1 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded-lg transition" title="Lecture seule">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <form action="{{ route('landlord.tenants.activate', $tenant) }}" method="POST" class="inline-block" onsubmit="return confirm('Réactiver cette boutique ?')">
                                        @csrf
                                        <button type="submit" class="p-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition" title="Activer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-450">Aucune boutique enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tenants->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $tenants->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
