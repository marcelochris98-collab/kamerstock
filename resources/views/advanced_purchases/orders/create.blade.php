@extends('layouts.app')

@section('title', 'Créer un Bon de Commande')
@section('page-title', 'Nouveau Bon de Commande')

@section('content')

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    @foreach($errors->all() as $error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('advanced_purchases.orders.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            ←
        </a>
        <div>
            <h1 class="text-sm font-semibold text-slate-800">Créer un Bon de Commande</h1>
            <p class="text-xs text-slate-400 mt-0.5">Saisir les articles à commander auprès du fournisseur sélectionné.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('advanced_purchases.orders.store') }}" class="bg-white rounded-xl shadow-sm p-6">
        @csrf

        {{-- Infos Fournisseur & Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fournisseur <span class="text-red-400">*</span></label>
                <select name="supplier_id" id="supplierId" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="">-- Sélectionner un fournisseur --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ (old('supplier_id') == $supplier->id || (isset($preselectedSupplierId) && $preselectedSupplierId == $supplier->id)) ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Date de Commande <span class="text-red-400">*</span></label>
                <input type="date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>

        {{-- Lignes d'articles --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-slate-700">Articles commandés</h2>
                <button type="button" onclick="addOrderRow()"
                    class="px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded hover:bg-slate-700 transition">
                    Ajouter une ligne
                </button>
            </div>

            <div id="orderItemsContainer" class="space-y-3">
                {{-- Lignes insérées en JS ou pré-chargées --}}
            </div>
        </div>

        {{-- Total --}}
        <div class="bg-slate-50 rounded-xl p-4 mb-5 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-600">Total estimé</span>
            <span class="text-sm font-bold text-slate-800" id="totalDisplay">0 FCFA</span>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
            <textarea name="notes" rows="3" placeholder="Instructions, conditions de livraison..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 resize-none">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                Enregistrer le Bon de Commande
            </button>
            <a href="{{ route('advanced_purchases.orders.index') }}"
                class="px-5 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<template id="orderRowTemplate">
    <div class="order-row border border-slate-100 rounded-xl p-4 bg-white space-y-3 relative">
        <button type="button" onclick="removeOrderRow(this)"
            class="absolute top-3 right-3 text-slate-400 hover:text-red-500 font-bold text-lg">
            ×
        </button>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-1">
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Produit</label>
                <select name="items[__INDEX__][product_id]" onchange="productChanged(this)"
                    class="product-select w-full px-2 py-1.5 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="">-- Sélectionner --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price_buy }}">
                            {{ $product->name }} (Réf: {{ $product->reference }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Quantité</label>
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1" oninput="calculateTotal()"
                    class="quantity-input w-full px-2 py-1.5 border border-slate-200 rounded text-xs text-center focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Prix d'Achat Estimé (FCFA)</label>
                <input type="number" name="items[__INDEX__][unit_price]" value="0" min="0" oninput="calculateTotal()"
                    class="price-input w-full px-2 py-1.5 border border-slate-200 rounded text-xs text-right focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
let rowIndex = 0;
const container = document.getElementById('orderItemsContainer');
const template = document.getElementById('orderRowTemplate');
const totalDisplay = document.getElementById('totalDisplay');

function addOrderRow(prefilledProductId = '', prefilledQty = 1, prefilledPrice = 0) {
    const html = template.innerHTML.replaceAll('__INDEX__', rowIndex);
    container.insertAdjacentHTML('beforeend', html);
    
    const row = container.lastElementChild;
    const select = row.querySelector('.product-select');
    const qtyInput = row.querySelector('.quantity-input');
    const priceInput = row.querySelector('.price-input');
    
    if (prefilledProductId) {
        select.value = prefilledProductId;
        qtyInput.value = prefilledQty;
        priceInput.value = prefilledPrice;
    }
    
    rowIndex++;
    calculateTotal();
}

function removeOrderRow(button) {
    button.closest('.order-row').remove();
    calculateTotal();
}

function productChanged(select) {
    const option = select.options[select.selectedIndex];
    const price = option.dataset.price || 0;
    const row = select.closest('.order-row');
    const priceInput = row.querySelector('.price-input');
    priceInput.value = price;
    calculateTotal();
}

function calculateTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.order-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        grandTotal += qty * price;
    });
    totalDisplay.textContent = grandTotal.toLocaleString('fr-FR') + ' FCFA';
}

// Charger les produits pré-sélectionnés s'il y en a
@if(count($preselectedProducts) > 0)
    @foreach($preselectedProducts as $prod)
        @php
            $suggestedQty = max(10, ($prod->alert_threshold * 2) - $prod->quantity);
        @endphp
        addOrderRow({{ $prod->id }}, {{ $suggestedQty }}, {{ $prod->price_buy }});
    @endforeach
@else
    addOrderRow();
@endif
</script>
@endpush
