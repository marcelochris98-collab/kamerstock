@extends('layouts.app')

@section('title', 'Ventes')
@section('page-title', 'Historique des ventes')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Historique des ventes</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $sales->total() }} vente(s)</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('export', 'sales') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Exporter CSV
        </a>
        @if(auth()->user()->hasPermission('sales.create'))
        <a href="{{ route('sales.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle vente
        </a>
        @endif
    </div>
</div>

{{-- Filtres --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher client, téléphone, N°..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div>
            <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous les statuts</option>
                <option value="completee" {{ request('status') === 'completee' ? 'selected' : '' }}>Complétée</option>
                <option value="credit" {{ request('status') === 'credit' ? 'selected' : '' }}>Crédit</option>
                <option value="annulee" {{ request('status') === 'annulee' ? 'selected' : '' }}>Annulée</option>
            </select>
        </div>
        <div>
            <select name="payment_mode" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous les modes</option>
                <option value="cash" {{ request('payment_mode') === 'cash' ? 'selected' : '' }}>Espèces</option>
                <option value="orange_money" {{ request('payment_mode') === 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                <option value="mtn_money" {{ request('payment_mode') === 'mtn_money' ? 'selected' : '' }}>MTN Money</option>
                <option value="credit" {{ request('payment_mode') === 'credit' ? 'selected' : '' }}>Crédit</option>
                <option value="mixte" {{ request('payment_mode') === 'mixte' ? 'selected' : '' }}>Mixte</option>
            </select>
        </div>
        <div class="flex gap-2">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-1/2 px-2 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-1/2 px-2 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                Filtrer
            </button>
            @if(request('search') || request('status') || request('payment_mode') || request('start_date') || request('end_date'))
            <a href="{{ route('sales.index') }}" class="py-2 px-3 border border-slate-200 text-slate-500 hover:text-slate-800 text-xs font-semibold rounded-lg hover:bg-slate-50 transition text-center flex items-center justify-center">
                Reset
            </a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">N°</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Caissier</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Client</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Articles</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Paiement</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Statut</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3 text-xs text-slate-400 font-mono">#{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td class="px-5 py-3 text-xs text-slate-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $sale->user->name }}</td>
                <td class="px-5 py-3 text-xs font-medium text-slate-700">
                    {{ $sale->client?->name ?? 'Passager' }}
                </td>
                <td class="px-5 py-3 text-xs text-slate-600">
                    <div class="flex flex-col gap-0.5 max-w-[200px]">
                        @foreach($sale->details as $detail)
                            <span class="truncate" title="{{ $detail->product ? $detail->product->name : 'Article inconnu' }}">
                                <span class="font-medium">{{ $detail->quantity }}x</span> {{ $detail->product ? $detail->product->name : 'Article inconnu' }}
                            </span>
                        @endforeach
                    </div>
                </td>
                <td class="px-5 py-3 text-right text-xs font-semibold text-slate-800">
                    {{ number_format($sale->total_amount, 0, ',', ' ') }} F
                </td>
                <td class="px-5 py-3 text-xs text-slate-500">{{ $sale->payment_mode_label }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        {{ $sale->status === 'completee' ? 'bg-emerald-50 text-emerald-700' :
                           ($sale->status === 'credit' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                        {{ $sale->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('sales.receipt', $sale) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-500 hover:border-blue-200 transition"
                            title="Recu">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </a>
                        @if(auth()->user()->hasPermission('sales.cancel') && $sale->status !== 'annulee')
                        <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                            onsubmit="return confirm('Annuler cette vente ? Le stock sera restaure.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 transition"
                                title="Annuler">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucune vente enregistree</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($sales->hasPages())
    <div class="px-5 py-3 border-t border-slate-50 flex items-center justify-between">
        <p class="text-xs text-slate-400">{{ $sales->firstItem() }} - {{ $sales->lastItem() }} sur {{ $sales->total() }}</p>
        <div class="flex gap-2">
            @if($sales->onFirstPage())
            <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Precedent</span>
            @else
            <a href="{{ $sales->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Precedent</a>
            @endif
            @if($sales->hasMorePages())
            <a href="{{ $sales->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Suivant</a>
            @else
            <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Suivant</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
