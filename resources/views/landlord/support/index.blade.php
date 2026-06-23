@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Accès Support Sécurisés</h1>
            <p class="text-xs text-slate-400 font-medium mt-1">Historique et sessions d'assistance temporaires sur les boutiques</p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('landlord.support.expire_old') }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-xs font-bold text-slate-700 rounded-xl transition select-none">
                    Nettoyer les accès expirés
                </button>
            </form>
            <a href="{{ route('landlord.support.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-md transition select-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Créer une demande
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Info Alert --}}
    <div class="mb-6 p-4 bg-slate-900 border border-slate-800 text-slate-400 rounded-xl text-xs flex items-center justify-between shadow-sm select-none">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><strong>Sécurité & Traçabilité</strong> : Les accès support sont temporaires, révocables et journalisés.</span>
        </div>
        <span class="text-[10px] text-slate-550 uppercase tracking-widest font-bold font-mono">Support Platform</span>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Motif de l'accès</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4">Période d'accès</th>
                        <th class="px-6 py-4">Révocation</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($accesses as $access)
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0">
                                        {{ strtoupper(substr($access->tenant?->name ?? 'B', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 leading-none">
                                            @if($access->tenant)
                                                <a href="{{ route('landlord.tenants.show', $access->tenant) }}" class="hover:text-indigo-650 underline">
                                                    {{ $access->tenant->name }}
                                                </a>
                                            @else
                                                Boutique inconnue
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-1 leading-none font-mono">ID: {{ $access->tenant_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium">
                                {{ Str::limit($access->reason, 50) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $access->statusBadgeClass() }}">
                                    {{ $access->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-650">
                                @if($access->starts_at && $access->ends_at)
                                    <p class="font-semibold text-slate-750">Du {{ $access->starts_at->format('d/m H:i') }} au {{ $access->ends_at->format('d/m H:i') }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Durée : {{ $access->durationLabel() }}</p>
                                @else
                                    <p class="text-slate-400">Non activé</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Demande : {{ $access->metadata['duration'] ?? '30 mins' }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($access->isRevoked())
                                    <p class="text-rose-700 font-semibold">Le {{ $access->revoked_at->format('d/m H:i') }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Par Admin ID: {{ $access->revoked_by }}</p>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('landlord.support.show', $access) }}" class="inline-flex items-center justify-center p-1 bg-slate-100 hover:bg-slate-200 text-slate-650 rounded-lg transition" title="Détails">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @if($access->isPending())
                                    <form action="{{ route('landlord.support.activate', $access) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" class="p-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-lg transition" title="Activer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                @if($access->canBeUsed())
                                    <a href="{{ route('landlord.support.enter', $access) }}" class="inline-flex items-center justify-center p-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition" title="Entrer en support">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                    </a>

                                    <form action="{{ route('landlord.support.revoke', $access) }}" method="POST" class="inline-block" onsubmit="return confirm('Révoquer immédiatement cet accès support ?')">
                                        @csrf
                                        <button type="submit" class="p-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Révoquer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-450">Aucun accès support configuré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($accesses->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $accesses->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
