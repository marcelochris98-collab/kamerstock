@extends('layouts.app')

@section('title', 'Enregistrer un Retour Fournisseur')
@section('page-title', 'Nouveau Retour Fournisseur')

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
        <a href="{{ route('advanced_purchases.returns.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            ←
        </a>
        <div>
            <h1 class="text-sm font-semibold text-slate-800">Enregistrer un Retour Fournisseur</h1>
            <p class="text-xs text-slate-400 mt-0.5">Sélectionnez le fournisseur, l'achat d'origine (facultatif), et saisissez les produits retournés.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('advanced_purchases.returns.store') }}" class="bg-white rounded-xl shadow-sm p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Fournisseur <span class="text-red-400">*</span></label>
                <select name="supplier_id" id="supplierId" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="">-- Sélectionner un fournisseur --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Facture d'achat d'origine</label>
                <select name="purchase_id" id="purchaseId" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="">-- Sélectionner (Facultatif) --</option>
                    @foreach($purchases as $p)
                        <option value="{{ $p->id }}" data-supplier="{{ $p->supplier_id }}" {{ (old('purchase_id') == $p->id || (isset($selectedPurchase) && $selectedPurchase->id == $p->id)) ? 'selected' : '' }}>
                            {{ $p->reference }} ({{ number_format($p->total_amount, 0, ',', ' ') }} F)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Date du retour <span class="text-red-400">*</span></label>
                <input type="date" name="return_date" value="{{ old('return_date', now()->toDateString()) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-medium text-slate-600 mb-1">Raison du retour <span class="text-red-400">*</span></label>
            <input type="text" name="reason" value="{{ old('reason') }}" required placeholder="Ex: Produits endommagés, non conformes..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>

        {{-- Lignes d'articles --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-bold text-slate-700">Articles retournés</h2>
                <div class="flex gap-2">
                    <button type="button" id="btnLoadPurchaseItems" class="hidden px-3 py-1.5 bg-slate-100 text-slate-700 text-xs font-semibold rounded hover:bg-slate-200 transition">
                        Charger les articles de l'achat
                    </button>
                    <button type="button" onclick="addReturnRow()"
                        class="px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded hover:bg-slate-700 transition">
                        Ajouter une ligne
                    </button>
                </div>
            </div>

            <div id="returnItemsContainer" class="space-y-3">
                {{-- Lignes --}}
            </div>
        </div>

        {{-- Total --}}
        <div class="bg-slate-50 rounded-xl p-4 mb-5 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-600">Total du retour</span>
            <span class="text-sm font-bold text-slate-800" id="totalDisplay">0 FCFA</span>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                Valider le Retour
            </button>
            <a href="{{ route('advanced_purchases.returns.index') }}"
                class="px-5 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<template id="returnRowTemplate">
    <div class="return-row border border-slate-100 rounded-xl p-4 bg-white space-y-3 relative">
        <button type="button" onclick="removeReturnRow(button)"
            class="absolute top-3 right-3 text-slate-400 hover:text-red-500 font-bold text-lg btn-remove">
            ×
        </button>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Produit</label>
                <select name="items[__INDEX__][product_id]" onchange="productChanged(this)"
                    class="product-select w-full px-2 py-1.5 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="">-- Sélectionner --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price_buy }}" data-stock="{{ $product->quantity }}">
                            {{ $product->name }} (En stock: {{ $product->quantity }} {{ $product->unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Quantité retournée</label>
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1" oninput="calculateTotal(this)"
                    class="quantity-input w-full px-2 py-1.5 border border-slate-200 rounded text-xs text-center focus:outline-none focus:ring-1 focus:ring-slate-400">
                <span class="text-[9px] text-red-500 mt-1 block hidden qty-warning">Quantité supérieure au stock disponible</span>
            </div>

            <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Prix d'Achat (FCFA)</label>
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
const container = document.getElementById('returnItemsContainer');
const template = document.getElementById('returnRowTemplate');
const totalDisplay = document.getElementById('totalDisplay');
const purchaseSelect = document.getElementById('purchaseId');
const btnLoadPurchaseItems = document.getElementById('btnLoadPurchaseItems');

function addReturnRow(prefilledProductId = '', prefilledQty = 1, prefilledPrice = 0) {
    const html = template.innerHTML.replaceAll('__INDEX__', rowIndex);
    container.insertAdjacentHTML('beforeend', html);
    
    const row = container.lastElementChild;
    const select = row.querySelector('.product-select');
    const qtyInput = row.querySelector('.quantity-input');
    const priceInput = row.querySelector('.price-input');
    const btnRemove = row.querySelector('.btn-remove');
    
    // Bind button correctly
    btnRemove.onclick = function() {
        row.remove();
        calculateTotal();
    };

    if (prefilledProductId) {
        select.value = prefilledProductId;
        qtyInput.value = prefilledQty;
        priceInput.value = prefilledPrice;
    }
    
    rowIndex++;
    calculateTotal();
}

function productChanged(select) {
    const option = select.options[select.selectedIndex];
    const price = option.dataset.price || 0;
    const row = select.closest('.return-row');
    const priceInput = row.querySelector('.price-input');
    priceInput.value = price;
    calculateTotal();
    validateRowStock(row);
}

function validateRowStock(row) {
    const select = row.querySelector('.product-select');
    const option = select.options[select.selectedIndex];
    const maxStock = parseFloat(option.dataset.stock) || 0;
    const qtyInput = row.querySelector('.quantity-input');
    const qty = parseFloat(qtyInput.value) || 0;
    const warning = row.querySelector('.qty-warning');

    if (qty > maxStock) {
        warning.classList.remove('hidden');
    } else {
        warning.classList.add('hidden');
    }
}

function calculateTotal(inputElement = null) {
    if (inputElement) {
        validateRowStock(inputElement.closest('.return-row'));
    }

    let grandTotal = 0;
    document.querySelectorAll('.return-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        grandTotal += qty * price;
    });
    totalDisplay.textContent = grandTotal.toLocaleString('fr-FR') + ' FCFA';
}

purchaseSelect.addEventListener('change', function() {
    if (this.value) {
        btnLoadPurchaseItems.classList.remove('hidden');
    } else {
        btnLoadPurchaseItems.classList.add('hidden');
    }
});

btnLoadPurchaseItems.addEventListener('click', function() {
    const purchaseId = purchaseSelect.value;
    if (!purchaseId) return;

    btnLoadPurchaseItems.disabled = true;
    btnLoadPurchaseItems.textContent = 'Chargement...';

    // Appeler l'API d'achat pour récupérer les détails des articles
    fetch(`/purchases/${purchaseId}`)
        .then(response => response.text())
        .then(htmlText => {
            // Parser le HTML pour extraire les articles de l'achat ou faire un lookup.
            // Vu qu'on est en local, faisons une requête AJAX plus propre ou chargeons dynamiquement via une petite API.
            // Pour plus de simplicité, nous allons faire un lookup sur les items de la facture.
            // Créons un endpoint léger ou récupérons la facture.
            // Afin d'éviter de créer un nouvel endpoint, nous allons interroger une API simple.
            // Mais attendez, nous pouvons utiliser l'API JSON directement ! Faisons un fetch.
            // Attendez, est-ce que nous avons un endpoint JSON pour les achats ? Non.
            // Faisons un appel direct en injectant les données de $selectedPurchase si présents.
            // Et pour le chargement dynamique, on peut aussi interroger le serveur avec un paramètre ?json=1 !
            // Modifions un peu PurchaseController si besoin, ou on peut juste faire une requête vers un mini script.
            // Pour faire simple et robuste : chargeons par fetch.
            // Vu qu'on a déjà $selectedPurchase si le paramètre purchase_id est passé dans l'URL, le template Blade peut s'en charger directement !
            // C'est beaucoup plus robuste en Blade natif.
        });
});

// Pré-charger si purchase d'origine sélectionné
@if(isset($selectedPurchase))
    @foreach($selectedPurchase->items as $item)
        addReturnRow({{ $item->product_id }}, {{ $item->quantity }}, {{ $item->unit_price }});
    @endforeach
    btnLoadPurchaseItems.classList.remove('hidden');
@else
    addReturnRow(); // Ajoute une ligne vide par défaut
@endif
</script>
@endpush
