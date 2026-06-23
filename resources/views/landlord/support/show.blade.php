@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('landlord.support.index') }}" class="text-xs text-indigo-600 hover:text-indigo-850 font-bold flex items-center gap-1 mb-2 select-none">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour à la liste
        </a>
        <h1 class="text-xl font-bold text-slate-900">Détails de l'Accès Support</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">ID d'autorisation : {{ $access->id }}</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Main Details Card --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Informations d'autorisation</h3>

                <ul class="space-y-4 text-xs">
                    <li class="flex flex-col gap-1">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Boutique cible</span>
                        <span class="text-slate-850 font-bold text-sm">
                            @if($access->tenant)
                                {{ $access->tenant->name }} ({{ $access->tenant->slug }})
                            @else
                                Boutique inconnue
                            @endif
                        </span>
                    </li>
                    <li class="flex flex-col gap-1">
                        <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Motif de l'intervention</span>
                        <span class="text-slate-800 font-medium text-xs bg-slate-50 border border-slate-150 rounded-xl p-3 leading-relaxed select-all">
                            {{ $access->reason }}
                        </span>
                    </li>
                    <li class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Demandé par</span>
                            <span class="font-semibold text-slate-800">Utilisateur ID: {{ $access->requested_by ?? 'N/A' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Accordé à</span>
                            <span class="font-semibold text-slate-800">Utilisateur ID: {{ $access->granted_to ?? 'N/A' }}</span>
                        </div>
                    </li>
                    <li class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Date de début</span>
                            <span class="font-semibold text-slate-800">{{ $access->starts_at ? $access->starts_at->format('d/m/Y H:i') : 'Non activée' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Date d'expiration</span>
                            <span class="font-semibold text-slate-800">{{ $access->ends_at ? $access->ends_at->format('d/m/Y H:i') : 'Non activée' }}</span>
                        </div>
                    </li>
                    @if($access->isRevoked())
                        <li class="flex flex-col gap-1 border-t border-slate-100 pt-4 bg-rose-50/50 border border-rose-150 rounded-xl p-3">
                            <span class="text-rose-700 font-bold uppercase tracking-wider text-[10px]">Révocation</span>
                            <p class="text-rose-900 font-semibold text-xs">Accès révoqué manuellement le {{ $access->revoked_at->format('d/m/Y à H:i') }}</p>
                            <p class="text-rose-800 text-[10px] mt-0.5 font-medium">Révoqué par : Admin ID {{ $access->revoked_by }}</p>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Status & Quick Actions --}}
        <div class="space-y-6">
            
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Statut actuel</h3>

                <div class="mb-5 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-semibold">Statut</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $access->statusBadgeClass() }}">
                        {{ $access->statusLabel() }}
                    </span>
                </div>

                <div class="space-y-3">
                    @if($access->isPending())
                        <form action="{{ route('landlord.support.activate', $access) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition text-xs select-none">
                                Activer l'accès support
                            </button>
                        </form>
                    @endif

                    @if($access->canBeUsed())
                        <a href="{{ route('landlord.support.enter', $access) }}" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-center block transition text-xs shadow-md shadow-indigo-650/10 select-none">
                            Entrer en support
                        </a>

                        <form action="{{ route('landlord.support.revoke', $access) }}" method="POST" onsubmit="return confirm('Révoquer immédiatement cet accès support ?')">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-red-50 hover:bg-red-100 text-red-650 font-bold rounded-xl transition text-xs border border-red-200 select-none">
                                Révoquer l'accès support
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('landlord.support.index') }}" class="w-full py-2 bg-slate-50 hover:bg-slate-100 text-slate-650 font-semibold text-center block rounded-xl border border-slate-200 transition text-xs select-none">
                        Retour à l'index
                    </a>
                </div>
            </div>

            <div class="p-4 bg-slate-900 text-slate-400 border border-slate-800 rounded-2xl text-[10px] leading-relaxed">
                <span class="block font-bold text-white uppercase mb-1 tracking-wider">Avertissement de confidentialité</span>
                Ces accès sont strictement temporaires, liés à l'utilisateur connecté, révocables et audités.
                Aucune donnée sensible (mot de passe propriétaire, etc.) n'est exposée.
            </div>

        </div>

    </div>

</div>
@endsection
