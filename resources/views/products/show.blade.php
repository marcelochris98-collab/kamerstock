@extends('layouts.app')

@section('title', $product->name)
@section('page-title', $product->name)

@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('products.index') }}"
        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h1 class="text-sm font-semibold text-slate-800">{{ $product->name }}</h1>
    @if(auth()->user()->hasPermission('products.edit'))
    <a href="{{ route('products.edit', $product) }}"
        class="ml-auto flex items-center gap-2 px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Modifier
    </a>
    @endif
</div>

<div class="grid grid-cols-3 gap-5">

    {{-- Infos principales --}}
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Informations</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Reference</p>
                    <p class="text-xs font-mono font-semibold text-slate-700">{{ $product->reference }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Categorie</p>
                    <p class="text-xs font-medium text-slate-700">{{ $product->category?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Fournisseur</p>
                    <p class="text-xs font-medium text-slate-700">{{ $product->supplier?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Unite</p>
                    <p class="text-xs font-medium text-slate-700">{{ $product->unit_label }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Prix achat</p>
                    <p class="text-xs font-semibold text-slate-700">{{ number_format($product->price_buy, 0, ',', ' ') }} FCFA</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Prix vente</p>
                    <p class="text-xs font-semibold text-slate-700">{{ number_format($product->price_sell, 0, ',', ' ') }} FCFA</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Marge</p>
                    <p class="text-xs font-semibold {{ $product->margin >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ number_format($product->margin, 0, ',', ' ') }} FCFA
                        ({{ $product->margin_percent }}%)
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 mb-0.5">Taux TVA</p>
                    <p class="text-xs font-medium text-slate-700">{{ $product->tax_rate }}%</p>
                </div>
            </div>
            @if($product->description)
            <div class="mt-4 pt-4 border-t border-slate-50">
                <p class="text-xs text-slate-400 mb-1">Description</p>
                <p class="text-xs text-slate-600">{{ $product->description }}</p>
            </div>
            @endif
        </div>

        {{-- Derniers mouvements --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50">
                <p class="text-xs font-semibold text-slate-700">Derniers mouvements de stock</p>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50">
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-slate-400">Date</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-slate-400">Type</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-slate-400">Quantite</th>
                        <th class="px-5 py-2.5 text-left text-xs font-medium text-slate-400">Motif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->stockMovements as $mvt)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="px-5 py-2.5 text-xs text-slate-400">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-2.5">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                {{ $mvt->type === 'entree' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                {{ $mvt->type === 'entree' ? 'Entree' : 'Sortie' }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-xs font-semibold text-slate-700">{{ $mvt->quantity }}</td>
                        <td class="px-5 py-2.5 text-xs text-slate-500">{{ $mvt->reason ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-xs text-slate-400">Aucun mouvement</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stock --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Stock</h2>
            <div class="text-center py-4">
                <p class="text-4xl font-bold {{ $product->isLowStock() ? 'text-red-500' : 'text-slate-800' }}">
                    {{ $product->quantity }}
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ $product->unit_label }}</p>
            </div>
            <div class="border-t border-slate-50 pt-4 mt-2">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-400">Seuil alerte</p>
                    <p class="text-xs font-medium text-slate-600">{{ $product->alert_threshold }}</p>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-400">Valeur stock</p>
                    <p class="text-xs font-semibold text-slate-700">
                        {{ number_format($product->price_buy * $product->quantity, 0, ',', ' ') }} F
                    </p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-400">Statut</p>
                    <span class="px-2 py-0.5 rounded text-xs font-semibold
                        {{ $product->isLowStock() ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $product->isLowStock() ? 'Stock bas' : 'Normal' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
