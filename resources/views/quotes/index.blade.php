@extends('layouts.app')

@section('title', 'Devis & Proformas')
@section('page-title', 'Devis & Proformas')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-semibold text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Devis & Proformas</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $quotes->total() }} document(s) enregistré(s)</p>
    </div>
    
    <a href="{{ route('quotes.create') }}"
        class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
        Créer un devis/proforma
    </a>
</div>

<!-- Filtres -->
<div class="bg-white rounded-xl shadow-sm p-4 mb-5 border border-slate-100">
    <form method="GET" action="{{ route('quotes.index') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
        <div class="sm:col-span-2">
            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence, client..."
                class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        
        <div>
            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Type</label>
            <select name="type" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous</option>
                <option value="devis" {{ request('type') == 'devis' ? 'selected' : '' }}>Devis</option>
                <option value="proforma" {{ request('type') == 'proforma' ? 'selected' : '' }}>Proforma</option>
            </select>
        </div>

        <div>
            <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Statut</label>
            <select name="status" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous</option>
                <option value="brouillon" {{ request('status') == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="envoye" {{ request('status') == 'envoye' ? 'selected' : '' }}>Envoyé</option>
                <option value="valide" {{ request('status') == 'valide' ? 'selected' : '' }}>Validé</option>
                <option value="converti" {{ request('status') == 'converti' ? 'selected' : '' }}>Converti en vente</option>
            </select>
        </div>

        <div class="flex items-end">
            <button type="submit" class="w-full px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Liste -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden border border-slate-100">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[800px]">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70 text-slate-500">
                    <th class="px-5 py-3 text-left text-xs font-medium">Référence</th>
                    <th class="px-5 py-3 text-left text-xs font-medium">Client</th>
                    <th class="px-5 py-3 text-center text-xs font-medium">Type</th>
                    <th class="px-5 py-3 text-right text-xs font-medium">Montant HT</th>
                    <th class="px-5 py-3 text-right text-xs font-medium">Montant TTC</th>
                    <th class="px-5 py-3 text-center text-xs font-medium">Statut</th>
                    <th class="px-5 py-3 text-right text-xs font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-slate-700">
                @forelse($quotes as $quote)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-5 py-3 text-xs font-bold text-slate-900">
                        {{ $quote->reference }}
                        <p class="text-[10px] text-slate-400 font-normal mt-0.5">Créé le {{ $quote->created_at->format('d/m/Y') }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs">
                        <p class="font-semibold text-slate-800">{{ $quote->client->name ?? 'Passager' }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $quote->client->phone ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $quote->type === 'devis' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-pink-50 text-pink-700 border border-pink-100' }}">
                            {{ $quote->type_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-xs font-semibold text-slate-600">
                        {{ number_format($quote->subtotal, 0, ',', ' ') }} F
                    </td>
                    <td class="px-5 py-3 text-right text-xs font-bold text-slate-800">
                        {{ number_format($quote->total_amount, 0, ',', ' ') }} F
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                            {{ $quote->status === 'converti' ? 'bg-emerald-100 text-emerald-800' : 
                               ($quote->status === 'brouillon' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-800') }}">
                            {{ $quote->status_label }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('quotes.show', $quote->id) }}"
                                class="px-2.5 py-1 border border-slate-200 rounded text-[10px] font-bold text-slate-600 hover:bg-slate-50 transition">
                                Voir
                            </a>
                            <a href="{{ route('quotes.print', $quote->id) }}" target="_blank"
                                class="px-2.5 py-1 border border-slate-200 rounded text-[10px] font-bold text-slate-600 hover:bg-slate-50 transition">
                                Imprimer
                            </a>
                            @if($quote->status !== 'converti')
                            <form method="POST" action="{{ route('quotes.convert', $quote->id) }}" onsubmit="return confirm('Convertir ce devis en vente et décrémenter le stock ?')">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 bg-slate-900 text-white rounded text-[10px] font-bold hover:bg-slate-800 transition">
                                    Facturer
                                </button>
                            </form>
                            <form method="POST" action="{{ route('quotes.destroy', $quote->id) }}" onsubmit="return confirm('Supprimer ce document ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-xs text-slate-400 font-medium">
                        Aucun document trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotes->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $quotes->links() }}
    </div>
    @endif
</div>

@endsection
