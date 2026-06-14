@extends('layouts.app')

@section('title', 'Nouveau produit')
@section('page-title', 'Nouveau produit')

@section('content')

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('products.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-sm font-semibold text-slate-800">Ajouter un produit</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Nom du produit <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Ex: Ciment Portland"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Référence <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                        placeholder="Ex: CIM-001"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('reference') border-red-300 @enderror">
                    @error('reference') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
<div>
    <label class="block text-xs font-medium text-slate-600 mb-1">Catégorie</label>

    <input type="hidden" name="category_id" id="categoryId" value="{{ old('category_id') }}">

    <input type="text" name="category_name" id="categoryName" value="{{ old('category_name') }}"
        placeholder="Ex: Ciment, Outillage, Plomberie"
        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

    <div id="categoryLookupBox" class="hidden mt-2 p-3 rounded-lg border border-amber-100 bg-amber-50"></div>

    <p id="categorySelectedText" class="hidden mt-2 text-xs font-medium text-emerald-600"></p>
</div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Unité <span class="text-red-400">*</span></label>
                    <select name="unit"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="piece" {{ old('unit') == 'piece' ? 'selected' : '' }}>Pièce</option>
                        <option value="metre" {{ old('unit') == 'metre' ? 'selected' : '' }}>Mètre</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogramme</option>
                        <option value="litre" {{ old('unit') == 'litre' ? 'selected' : '' }}>Litre</option>
                        <option value="boite" {{ old('unit') == 'boite' ? 'selected' : '' }}>Boîte</option>
                        <option value="sachet" {{ old('unit') == 'sachet' ? 'selected' : '' }}>Sachet</option>
                    </select>
                </div>
            </div>

            {{-- Fournisseur intelligent --}}
            <div class="mb-4 p-4 rounded-xl border border-slate-100 bg-slate-50">
                <label class="block text-xs font-semibold text-slate-700 mb-3">Fournisseur</label>

                <input type="hidden" name="supplier_id" id="supplierId" value="{{ old('supplier_id') }}">

                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="supplier_name" id="supplierName" value="{{ old('supplier_name') }}"
                        placeholder="Nom du fournisseur"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                    <input type="text" name="supplier_phone" id="supplierPhone" value="{{ old('supplier_phone') }}"
                        placeholder="Téléphone"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>

                <input type="email" name="supplier_email" id="supplierEmail" value="{{ old('supplier_email') }}"
                    placeholder="Email fournisseur facultatif"
                    class="w-full mt-3 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <div id="supplierLookupBox" class="hidden mt-3 p-3 rounded-lg border border-amber-100 bg-amber-50"></div>

                <p id="supplierSelectedText" class="hidden mt-2 text-xs font-medium text-emerald-600"></p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix achat (FCFA) <span class="text-red-400">*</span></label>
                    <input type="number" name="price_buy" value="{{ old('price_buy', 0) }}" min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('price_buy') border-red-300 @enderror">
                    @error('price_buy') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix vente particulier (FCFA) <span class="text-red-400">*</span></label>
                    <input type="number" name="price_sell" value="{{ old('price_sell', 0) }}" min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('price_sell') border-red-300 @enderror">
                    @error('price_sell') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix entreprise (FCFA)</label>
                    <input type="number" name="price_sell_company" value="{{ old('price_sell_company') }}" min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix revendeur (FCFA)</label>
                    <input type="number" name="price_sell_reseller" value="{{ old('price_sell_reseller') }}" min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Prix grossiste (FCFA)</label>
                    <input type="number" name="price_sell_wholesale" value="{{ old('price_sell_wholesale') }}" min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Quantité initiale <span class="text-red-400">*</span></label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}" min="0"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('quantity') border-red-300 @enderror">
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Seuil alerte stock <span class="text-red-400">*</span></label>
                    <input type="number" name="alert_threshold" value="{{ old('alert_threshold', 5) }}" min="0"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('alert_threshold') border-red-300 @enderror">
                    @error('alert_threshold') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                <textarea name="description" rows="3"
                    placeholder="Description optionnelle du produit..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 resize-none">{{ old('description') }}</textarea>
            </div>

            <div class="bg-slate-50 rounded-lg p-3 mb-6" id="margePreview">
                <p class="text-xs text-slate-500">
                    Marge : <span id="margeValeur" class="font-semibold text-slate-700">0 FCFA</span>
                    <span id="margePct" class="text-slate-400 ml-2"></span>
                </p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Enregistrer le produit
                </button>

                <a href="{{ route('products.index') }}"
                    class="px-5 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const priceBuy = document.querySelector('[name="price_buy"]');
const priceSell = document.querySelector('[name="price_sell"]');

function updateMarge() {
    const buy = parseFloat(priceBuy.value) || 0;
    const sell = parseFloat(priceSell.value) || 0;
    const marge = sell - buy;
    const pct = buy > 0 ? ((marge / buy) * 100).toFixed(1) : 0;

    document.getElementById('margeValeur').textContent = marge.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('margeValeur').className = marge >= 0
        ? 'font-semibold text-emerald-600'
        : 'font-semibold text-red-500';

    document.getElementById('margePct').textContent = buy > 0 ? `(${pct}%)` : '';
}

priceBuy.addEventListener('input', updateMarge);
priceSell.addEventListener('input', updateMarge);
updateMarge();

let supplierLookupTimer = null;
let selectedSupplierLocked = false;

const supplierId = document.getElementById('supplierId');
const supplierName = document.getElementById('supplierName');
const supplierPhone = document.getElementById('supplierPhone');
const supplierEmail = document.getElementById('supplierEmail');
const supplierLookupBox = document.getElementById('supplierLookupBox');
const supplierSelectedText = document.getElementById('supplierSelectedText');

function lookupSupplier() {
    if (selectedSupplierLocked) return;

    const name = supplierName.value.trim();
    const phone = supplierPhone.value.trim();

    supplierId.value = '';
    supplierSelectedText.classList.add('hidden');
    supplierSelectedText.textContent = '';

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
                        Aucun fournisseur trouvé. Il sera créé automatiquement avec le produit.
                    </p>
                `;
                return;
            }

            let html = `<p class="text-xs font-semibold text-amber-700 mb-2">Fournisseur déjà enregistré ou similaire :</p>`;

            data.suppliers.forEach(supplier => {
                const safeName = String(supplier.name).replace(/'/g, "\\'");
                const safePhone = String(supplier.phone ?? '').replace(/'/g, "\\'");
                const safeEmail = String(supplier.email ?? '').replace(/'/g, "\\'");

                html += `
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-amber-100 first:border-t-0">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">${supplier.name}</p>
                            <p class="text-xs text-slate-500">${supplier.phone ?? '—'} ${supplier.email ? '— ' + supplier.email : ''}</p>
                        </div>

                        <button type="button"
                            onclick="useExistingSupplier(${supplier.id}, '${safeName}', '${safePhone}', '${safeEmail}')"
                            class="px-2 py-1 bg-slate-900 text-white rounded text-xs font-semibold">
                            Utiliser
                        </button>
                    </div>
                `;
            });

            supplierLookupBox.innerHTML = html;
        });
}

function useExistingSupplier(id, name, phone, email) {
    selectedSupplierLocked = true;

    supplierId.value = id;
    supplierName.value = name;
    supplierPhone.value = phone;
    supplierEmail.value = email;

    supplierLookupBox.classList.add('hidden');
    supplierLookupBox.innerHTML = '';

    supplierSelectedText.textContent = `Fournisseur existant sélectionné : ${name}`;
    supplierSelectedText.classList.remove('hidden');

    setTimeout(() => {
        selectedSupplierLocked = false;
    }, 800);
}

[supplierName, supplierPhone].forEach(input => {
    input.addEventListener('input', function () {
        selectedSupplierLocked = false;
        clearTimeout(supplierLookupTimer);
        supplierLookupTimer = setTimeout(lookupSupplier, 500);
    });

    input.addEventListener('blur', function () {
        clearTimeout(supplierLookupTimer);
        supplierLookupTimer = setTimeout(lookupSupplier, 200);
    });
});
let categoryLookupTimer = null;
let selectedCategoryLocked = false;

const categoryId = document.getElementById('categoryId');
const categoryName = document.getElementById('categoryName');
const categoryLookupBox = document.getElementById('categoryLookupBox');
const categorySelectedText = document.getElementById('categorySelectedText');

function lookupCategory() {
    if (selectedCategoryLocked) return;

    const name = categoryName.value.trim();

    categoryId.value = '';
    categorySelectedText.classList.add('hidden');
    categorySelectedText.textContent = '';

    if (name.length < 3) {
        categoryLookupBox.classList.add('hidden');
        categoryLookupBox.innerHTML = '';
        return;
    }

    categoryLookupBox.classList.remove('hidden');
    categoryLookupBox.innerHTML = `<p class="text-xs text-slate-500">Vérification de la catégorie...</p>`;

    fetch(`{{ route('categories.lookup') }}?name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.found) {
                categoryLookupBox.innerHTML = `
                    <p class="text-xs font-semibold text-emerald-700">Nouvelle catégorie</p>
                    <p class="text-xs text-emerald-600 mt-1">
                        Aucune catégorie trouvée. Elle sera créée automatiquement avec le produit.
                    </p>
                `;
                return;
            }

            let html = `<p class="text-xs font-semibold text-amber-700 mb-2">Catégorie déjà enregistrée ou similaire :</p>`;

            data.categories.forEach(category => {
                const safeName = String(category.name).replace(/'/g, "\\'");

                html += `
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-amber-100 first:border-t-0">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">${category.name}</p>
                        </div>

                        <button type="button"
                            onclick="useExistingCategory(${category.id}, '${safeName}')"
                            class="px-2 py-1 bg-slate-900 text-white rounded text-xs font-semibold">
                            Utiliser
                        </button>
                    </div>
                `;
            });

            categoryLookupBox.innerHTML = html;
        });
}

function useExistingCategory(id, name) {
    selectedCategoryLocked = true;

    categoryId.value = id;
    categoryName.value = name;

    categoryLookupBox.classList.add('hidden');
    categoryLookupBox.innerHTML = '';

    categorySelectedText.textContent = `Catégorie existante sélectionnée : ${name}`;
    categorySelectedText.classList.remove('hidden');

    setTimeout(() => {
        selectedCategoryLocked = false;
    }, 800);
}

categoryName.addEventListener('input', function () {
    selectedCategoryLocked = false;
    clearTimeout(categoryLookupTimer);
    categoryLookupTimer = setTimeout(lookupCategory, 500);
});

categoryName.addEventListener('blur', function () {
    clearTimeout(categoryLookupTimer);
    categoryLookupTimer = setTimeout(lookupCategory, 200);
});
</script>
@endpush
