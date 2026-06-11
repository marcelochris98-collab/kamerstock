@extends('layouts.app')

@section('title', 'Nouvel achat fournisseur')
@section('page-title', 'Nouvel achat fournisseur')

@section('content')

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    @foreach($errors->all() as $error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="max-w-6xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('purchases.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            ←
        </a>

        <div>
            <h1 class="text-sm font-semibold text-slate-800">Nouvel achat fournisseur</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Fournisseur existant ou nouveau, produit existant ou nouveau, dette précédente gérée séparément.
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('purchases.store') }}" class="bg-white rounded-xl shadow-sm p-6">
        @csrf

        {{-- Fournisseur --}}
        <div class="mb-5 p-4 rounded-xl border border-slate-100 bg-slate-50">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-semibold text-slate-700">Fournisseur</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Utiliser un fournisseur existant ou créer un nouveau.</p>
                </div>

                <button type="button" onclick="clearSupplierSelection()"
                    class="text-xs text-slate-400 hover:text-slate-700">
                    Réinitialiser
                </button>
            </div>

            <input type="hidden" name="supplier_id" id="supplierId" value="{{ old('supplier_id') }}">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <input type="text" name="supplier_name" id="supplierName" value="{{ old('supplier_name') }}"
                    placeholder="Nom fournisseur"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <input type="text" name="supplier_phone" id="supplierPhone" value="{{ old('supplier_phone') }}"
                    placeholder="Téléphone"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <input type="email" name="supplier_email" id="supplierEmail" value="{{ old('supplier_email') }}"
                    placeholder="Email facultatif"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div id="supplierLookupBox" class="hidden mt-3 p-3 rounded-lg border border-amber-100 bg-amber-50"></div>
            <p id="supplierSelectedText" class="hidden mt-2 text-xs font-medium text-emerald-600"></p>

            <div id="supplierDebtBox" class="hidden mt-3 px-4 py-3 rounded-lg bg-red-50 border border-red-100">
                <p class="text-xs text-red-400">Dette précédente fournisseur</p>
                <p class="text-sm font-bold text-red-600 mt-1" id="supplierDebtText">0 FCFA</p>
            </div>
        </div>

        {{-- Infos achat --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Date achat</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Mode paiement <span class="text-red-400">*</span></label>
                <select name="payment_method"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="cash">Espèces</option>
                    <option value="orange_money">Orange Money</option>
                    <option value="mtn_money">MTN Money</option>
                    <option value="virement">Virement</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Paiement achat courant</label>
                <input type="number" name="amount_paid" id="amountPaid" value="{{ old('amount_paid', 0) }}" min="0" step="1"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Paiement anciennes dettes</label>
                <input type="number" name="previous_debt_payment" id="previousDebtPayment" value="{{ old('previous_debt_payment', 0) }}" min="0" step="1"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>

        {{-- Produits achetés --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-xs font-semibold text-slate-700">Produits achetés</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Choisissez un produit existant ou créez un nouveau produit directement depuis l'achat.
                    </p>
                </div>

                <button type="button" onclick="addPurchaseRow()"
                    class="px-3 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                    Ajouter une ligne
                </button>
            </div>

            <div id="purchaseItemsBody" class="space-y-4"></div>
        </div>

        {{-- Totaux --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
            <div class="bg-slate-50 rounded-lg p-3">
                <p class="text-xs text-slate-400">Total achat courant</p>
                <p class="text-sm font-bold text-slate-800 mt-1" id="totalAmountText">0 FCFA</p>
            </div>

            <div class="bg-emerald-50 rounded-lg p-3">
                <p class="text-xs text-emerald-500">Payé sur achat courant</p>
                <p class="text-sm font-bold text-emerald-700 mt-1" id="amountPaidText">0 FCFA</p>
            </div>

            <div class="bg-amber-50 rounded-lg p-3">
                <p class="text-xs text-amber-500">Payé anciennes dettes</p>
                <p class="text-sm font-bold text-amber-700 mt-1" id="previousDebtPaymentText">0 FCFA</p>
            </div>

            <div class="bg-red-50 rounded-lg p-3">
                <p class="text-xs text-red-400">Reste achat courant</p>
                <p class="text-sm font-bold text-red-600 mt-1" id="amountDueText">0 FCFA</p>
            </div>
        </div>

        <div class="mb-6">
            <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
            <textarea name="notes" rows="3"
                placeholder="Notes optionnelles..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 resize-none">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                Enregistrer l'achat
            </button>

            <a href="{{ route('purchases.index') }}"
                class="px-5 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<template id="purchaseRowTemplate">
    <div class="purchase-row border border-slate-100 rounded-xl p-4 bg-white">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <select name="items[__INDEX__][type]" class="item-type px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    <option value="existing">Produit existant</option>
                    <option value="new">Nouveau produit</option>
                </select>

                <span class="text-xs text-slate-400">Ligne achat</span>
            </div>

            <button type="button" onclick="removePurchaseRow(this)"
                class="w-7 h-7 rounded-lg border border-red-100 text-red-400 hover:bg-red-50 transition">
                ×
            </button>
        </div>

        <div class="existing-product-block">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Produit existant</label>
                    <select name="items[__INDEX__][product_id]"
                        class="product-select w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                        <option value="">-- Produit --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                data-buy-price="{{ $product->price_buy }}"
                                data-sale-price="{{ $product->price_sell }}">
                                {{ $product->name }} — Stock: {{ $product->quantity }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix vente actuel</label>
                    <input type="text" class="current-sale-price w-full px-3 py-2 border border-slate-200 rounded-lg text-xs bg-slate-50" readonly>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nouveau prix vente</label>
                    <input type="number" name="items[__INDEX__][sale_price]" min="0" step="1"
                        class="sale-price-input w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                </div>
            </div>
        </div>

        <div class="new-product-block hidden">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom produit</label>
                    <input type="text" name="items[__INDEX__][product_name]"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs"
                        placeholder="Ex: Fer 12">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Référence</label>
                    <input type="text" name="items[__INDEX__][reference]"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs"
                        placeholder="Ex: FER-12">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Catégorie existante</label>
                    <select name="items[__INDEX__][category_id]"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                        <option value="">-- Aucune --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nouvelle catégorie</label>
                    <input type="text" name="items[__INDEX__][category_name]"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs"
                        placeholder="Si nouvelle">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Unité</label>
                    <select name="items[__INDEX__][unit]"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                        <option value="piece">Pièce</option>
                        <option value="metre">Mètre</option>
                        <option value="kg">Kg</option>
                        <option value="litre">Litre</option>
                        <option value="boite">Boîte</option>
                        <option value="sachet">Sachet</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix vente</label>
                    <input type="number" name="items[__INDEX__][sale_price]" min="0" step="1"
                        class="sale-price-input w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Seuil alerte</label>
                    <input type="number" name="items[__INDEX__][alert_threshold]" value="5" min="0"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mt-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Quantité</label>
                <input type="number" name="items[__INDEX__][quantity]" value="1" min="1"
                    class="quantity-input w-full px-3 py-2 border border-slate-200 rounded-lg text-xs text-center">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Prix achat</label>
                <input type="number" name="items[__INDEX__][unit_price]" value="0" min="0" step="1"
                    class="unit-price-input w-full px-3 py-2 border border-slate-200 rounded-lg text-xs text-right">
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 text-xs text-slate-600 pb-2">
                    <input type="checkbox" name="items[__INDEX__][update_prices]" value="1">
                    Mettre à jour prix
                </label>
            </div>

            <div class="md:col-span-2 bg-slate-50 rounded-lg p-3">
                <p class="text-xs text-slate-400">Sous-total</p>
                <p class="subtotal-text text-sm font-bold text-slate-800 mt-1">0 FCFA</p>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
let rowIndex = 0;

const body = document.getElementById('purchaseItemsBody');
const template = document.getElementById('purchaseRowTemplate');

const totalAmountText = document.getElementById('totalAmountText');
const amountPaidText = document.getElementById('amountPaidText');
const previousDebtPaymentText = document.getElementById('previousDebtPaymentText');
const amountDueText = document.getElementById('amountDueText');

const amountPaid = document.getElementById('amountPaid');
const previousDebtPayment = document.getElementById('previousDebtPayment');

const supplierId = document.getElementById('supplierId');
const supplierName = document.getElementById('supplierName');
const supplierPhone = document.getElementById('supplierPhone');
const supplierEmail = document.getElementById('supplierEmail');
const supplierLookupBox = document.getElementById('supplierLookupBox');
const supplierSelectedText = document.getElementById('supplierSelectedText');
const supplierDebtBox = document.getElementById('supplierDebtBox');
const supplierDebtText = document.getElementById('supplierDebtText');

let supplierLookupTimer = null;

function formatMoney(amount) {
    return Number(amount || 0).toLocaleString('fr-FR') + ' FCFA';
}

function addPurchaseRow() {
    const html = template.innerHTML.replaceAll('__INDEX__', rowIndex);
    body.insertAdjacentHTML('beforeend', html);
    rowIndex++;
    bindPurchaseEvents();
    calculatePurchaseTotals();
}

function removePurchaseRow(button) {
    button.closest('.purchase-row').remove();
    calculatePurchaseTotals();
}

function bindPurchaseEvents() {
    document.querySelectorAll('.purchase-row').forEach(row => {
        const typeSelect = row.querySelector('.item-type');
        const existingBlock = row.querySelector('.existing-product-block');
        const newBlock = row.querySelector('.new-product-block');
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const currentSalePrice = row.querySelector('.current-sale-price');

        typeSelect.onchange = function () {
            if (typeSelect.value === 'new') {
                existingBlock.classList.add('hidden');
                newBlock.classList.remove('hidden');
            } else {
                existingBlock.classList.remove('hidden');
                newBlock.classList.add('hidden');
            }

            calculatePurchaseTotals();
        };

        if (productSelect) {
            productSelect.onchange = function () {
                const selected = productSelect.options[productSelect.selectedIndex];
                const buyPrice = selected.getAttribute('data-buy-price') || 0;
                const salePrice = selected.getAttribute('data-sale-price') || 0;

                if (parseFloat(unitPriceInput.value || 0) <= 0) {
                    unitPriceInput.value = buyPrice;
                }

                currentSalePrice.value = formatMoney(salePrice);
                calculatePurchaseTotals();
            };
        }

        quantityInput.oninput = calculatePurchaseTotals;
        unitPriceInput.oninput = calculatePurchaseTotals;
    });
}

function calculatePurchaseTotals() {
    let total = 0;

    document.querySelectorAll('.purchase-row').forEach(row => {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const subtotal = quantity * unitPrice;

        total += subtotal;
        row.querySelector('.subtotal-text').textContent = formatMoney(subtotal);
    });

    const paid = parseFloat(amountPaid.value) || 0;
    const oldDebtPaid = parseFloat(previousDebtPayment.value) || 0;
    const due = Math.max(total - paid, 0);

    totalAmountText.textContent = formatMoney(total);
    amountPaidText.textContent = formatMoney(paid);
    previousDebtPaymentText.textContent = formatMoney(oldDebtPaid);
    amountDueText.textContent = formatMoney(due);
}

amountPaid.addEventListener('input', calculatePurchaseTotals);
previousDebtPayment.addEventListener('input', calculatePurchaseTotals);

function lookupSupplier() {
    const name = supplierName.value.trim();
    const phone = supplierPhone.value.trim();

    supplierId.value = '';
    supplierSelectedText.classList.add('hidden');

    if (phone.length < 6 && name.length < 3) {
        supplierLookupBox.classList.add('hidden');
        supplierLookupBox.innerHTML = '';
        return;
    }

    supplierLookupBox.classList.remove('hidden');
    supplierLookupBox.innerHTML = `<p class="text-xs text-slate-500">Vérification du fournisseur...</p>`;

    fetch(`{{ route('suppliers.lookup') }}?phone=${encodeURIComponent(phone)}&name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.found) {
                supplierLookupBox.innerHTML = `
                    <p class="text-xs font-semibold text-emerald-700">Nouveau fournisseur</p>
                    <p class="text-xs text-emerald-600 mt-1">
                        Aucun fournisseur trouvé. Il sera créé automatiquement avec l'achat.
                    </p>
                `;

                supplierDebtBox.classList.add('hidden');
                supplierDebtText.textContent = formatMoney(0);
                return;
            }

            let html = `<p class="text-xs font-semibold text-amber-700 mb-2">Fournisseur existant ou similaire :</p>`;

            data.suppliers.forEach(supplier => {
                const safeName = String(supplier.name).replace(/'/g, "\\'");
                const safePhone = String(supplier.phone ?? '').replace(/'/g, "\\'");
                const safeEmail = String(supplier.email ?? '').replace(/'/g, "\\'");
                const totalDue = Number(supplier.total_due ?? 0);

                html += `
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-amber-100 first:border-t-0">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">${supplier.name}</p>
                            <p class="text-xs text-slate-500">${supplier.phone ?? '—'} ${supplier.email ? '— ' + supplier.email : ''}</p>
                            <p class="text-xs text-red-500 mt-0.5">Dette précédente : ${formatMoney(totalDue)}</p>
                        </div>

                        <button type="button"
                            onclick="useExistingSupplier(${supplier.id}, '${safeName}', '${safePhone}', '${safeEmail}', ${totalDue})"
                            class="px-2 py-1 bg-slate-900 text-white rounded text-xs font-semibold">
                            Utiliser
                        </button>
                    </div>
                `;
            });

            supplierLookupBox.innerHTML = html;
        });
}

function useExistingSupplier(id, name, phone, email, totalDue = 0) {
    supplierId.value = id;
    supplierName.value = name;
    supplierPhone.value = phone;
    supplierEmail.value = email;

    supplierLookupBox.classList.add('hidden');
    supplierLookupBox.innerHTML = '';

    supplierSelectedText.textContent = `Fournisseur existant sélectionné : ${name}`;
    supplierSelectedText.classList.remove('hidden');

    supplierDebtText.textContent = formatMoney(totalDue);

    if (Number(totalDue) > 0) {
        supplierDebtBox.classList.remove('hidden');
    } else {
        supplierDebtBox.classList.add('hidden');
    }
}

function clearSupplierSelection() {
    supplierId.value = '';
    supplierName.value = '';
    supplierPhone.value = '';
    supplierEmail.value = '';

    supplierLookupBox.classList.add('hidden');
    supplierLookupBox.innerHTML = '';
    supplierSelectedText.classList.add('hidden');

    supplierDebtBox.classList.add('hidden');
    supplierDebtText.textContent = formatMoney(0);

    previousDebtPayment.value = 0;
    calculatePurchaseTotals();
}

[supplierName, supplierPhone].forEach(input => {
    input.addEventListener('input', function () {
        clearTimeout(supplierLookupTimer);
        supplierLookupTimer = setTimeout(lookupSupplier, 500);
    });

    input.addEventListener('blur', function () {
        clearTimeout(supplierLookupTimer);
        supplierLookupTimer = setTimeout(lookupSupplier, 200);
    });
});

addPurchaseRow();
</script>
@endpush