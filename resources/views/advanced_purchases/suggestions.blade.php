@extends('layouts.app')

@section('title', 'Suggestions de réapprovisionnement')
@section('page-title', 'Réapprovisionnement automatique')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Suggestions de réapprovisionnement</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $products->count() }} produit(s) sous le seuil critique de stock</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('advanced_purchases.orders.index') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Bons de commande
        </a>
    </div>
</div>

@forelse($groupedProducts as $supplierId => $items)
@php
    $supplier = $suppliers->get($supplierId);
@endphp
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h3 class="text-xs font-bold text-slate-800">{{ $supplier ? $supplier->name : 'Sans Fournisseur Attribué' }}</h3>
            @if($supplier)
            <p class="text-[10px] text-slate-500">{{ $supplier->phone ?? 'Pas de téléphone' }} — {{ $supplier->email ?? 'Pas d\'email' }}</p>
            @endif
        </div>
        @if($supplier)
        <div>
            <a href="{{ route('advanced_purchases.orders.create', ['supplier_id' => $supplier->id, 'products' => $items->pluck('id')->join(',')]) }}"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-900 text-white text-[11px] font-semibold rounded hover:bg-slate-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Générer Bon de Commande
            </a>
        </div>
        @endif
    </div>

    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50/50">
                <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-slate-400">Produit</th>
                <th class="px-5 py-2.5 text-center text-[11px] font-semibold text-slate-400">Seuil d'Alerte</th>
                <th class="px-5 py-2.5 text-center text-[11px] font-semibold text-slate-400">Stock Actuel</th>
                <th class="px-5 py-2.5 text-right text-[11px] font-semibold text-slate-400">Prix d'Achat Estimé</th>
                <th class="px-5 py-2.5 text-center text-[11px] font-semibold text-slate-400">Quantité Suggérée</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $product)
            @php
                // Règle de suggestion : combler le manque par rapport au double du seuil d'alerte, minimum 10 unités
                $suggestedQty = max(10, ($product->alert_threshold * 2) - $product->quantity);
            @endphp
            <tr class="border-b border-slate-50 hover:bg-slate-50/30 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $product->name }}</p>
                    <p class="text-[10px] text-slate-400">Réf : {{ $product->reference }} — Cat : {{ $product->category?->name ?? '—' }}</p>
                </td>
                <td class="px-5 py-3 text-center text-xs text-slate-600">
                    {{ $product->alert_threshold }} {{ $product->unit }}
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-50 text-red-600">
                        {{ $product->quantity }} {{ $product->unit }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right text-xs font-medium text-slate-800">
                    {{ number_format($product->price_buy, 0, ',', ' ') }} FCFA
                </td>
                <td class="px-5 py-3 text-center text-xs font-bold text-slate-700">
                    {{ $suggestedQty }} {{ $product->unit }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@empty
<div class="bg-white rounded-xl shadow-sm p-12 text-center">
    <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <p class="text-sm font-semibold text-slate-800">Tous les stocks sont au vert !</p>
    <p class="text-xs text-slate-400 mt-1">Aucun produit n'est actuellement en dessous de son seuil d'alerte.</p>
</div>
@endforelse

@endsection
