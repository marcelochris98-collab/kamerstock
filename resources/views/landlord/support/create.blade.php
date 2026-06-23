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
        <h1 class="text-xl font-bold text-slate-900">Demander un Accès Support</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Générer une clé d'accès sécurisée temporaire pour assister un client</p>
    </div>

    <div class="max-w-xl bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
        
        <form action="{{ route('landlord.support.store') }}" method="POST" class="space-y-5 text-xs">
            @csrf

            {{-- Tenant Selector --}}
            <div>
                <label for="tenant_id" class="block text-slate-450 font-bold uppercase tracking-wider mb-2">Boutique (Tenant)</label>
                @if(isset($tenant))
                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                    <div class="w-full bg-slate-100 border border-slate-200 rounded-xl p-3 font-semibold text-slate-700">
                        {{ $tenant->name }} (Slug: {{ $tenant->slug }})
                    </div>
                @else
                    <select id="tenant_id" name="tenant_id" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:outline-none focus:border-indigo-500 font-semibold">
                        <option value="">Sélectionnez une boutique...</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->name }} ({{ $t->slug }})
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('tenant_id')
                    <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Motif --}}
            <div>
                <label for="reason" class="block text-slate-450 font-bold uppercase tracking-wider mb-2">Motif de l'intervention</label>
                <textarea id="reason" name="reason" rows="4" required placeholder="Décrivez de manière précise la raison de votre intervention support..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-850 focus:outline-none focus:border-indigo-500 font-medium">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Durée --}}
            <div>
                <label for="duration" class="block text-slate-450 font-bold uppercase tracking-wider mb-2">Durée de l'autorisation</label>
                <select id="duration" name="duration" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 focus:outline-none focus:border-indigo-500 font-semibold">
                    <option value="30_minutes" {{ old('duration') == '30_minutes' ? 'selected' : '' }}>30 minutes</option>
                    <option value="1_hour" {{ old('duration') == '1_hour' ? 'selected' : '' }}>1 heure</option>
                    <option value="24_hours" {{ old('duration') == '24_hours' ? 'selected' : '' }}>24 heures</option>
                </select>
                @error('duration')
                    <p class="text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-850 text-white font-bold rounded-xl shadow-md transition text-xs select-none">
                    Créer la demande d'accès
                </button>
            </div>

        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-[10px] text-slate-450 leading-relaxed font-medium">
            <svg class="w-3.5 h-3.5 text-slate-400 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Cet accès est temporaire, révocable et journalisé. Il ne donne pas un droit permanent sur les données de la boutique.
        </div>

    </div>

</div>
@endsection
