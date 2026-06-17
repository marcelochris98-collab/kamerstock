@extends('layouts.app')

@section('title', 'Créer un Devis / Proforma')
@section('page-title', 'Nouveau document de vente')

@section('content')

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    <p class="text-xs font-semibold text-red-600 mb-1">Erreurs détectées :</p>
    @foreach($errors->all() as $error)
    <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('quotes.store') }}" id="quoteForm">
@csrf

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Produits disponibles --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50 flex items-center gap-3">
                <input type="text" id="searchProduct" placeholder="Rechercher un produit..."
                    class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 max-h-96 overflow-y-auto" id="productGrid">
                @foreach($products as $product)
                <button type="button"
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-stock="{{ $product->quantity }}"
                    data-price-particulier="{{ $product->price_sell }}"
                    data-price-entreprise="{{ $product->price_sell_company ?? $product->price_sell }}"
                    data-price-revendeur="{{ $product->price_sell_reseller ?? $product->price_sell }}"
                    data-price-grossiste="{{ $product->price_sell_wholesale ?? $product->price_sell }}"
                    onclick="addProductToQuote({{ $product->id }})"
                    class="flex items-start gap-3 p-3 border border-slate-100 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition text-left product-card product-card-btn"
                    data-name="{{ strtolower($product->name) }}">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-slate-400">{{ $product->reference }}</p>
                        <p class="text-xs font-bold text-slate-700 mt-1">{{ number_format($product->price_sell, 0, ',', ' ') }} F</p>
                        <p class="text-xs text-slate-400">Stock : {{ $product->quantity }}</p>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Panier + Client --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Panier --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-50">
                <p class="text-xs font-semibold text-slate-800">Articles du document</p>
            </div>

            <div id="cartItems" class="divide-y divide-slate-50 min-h-16">
                <p class="px-4 py-6 text-center text-xs text-slate-400">Document vide</p>
            </div>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50 text-xs text-slate-600 space-y-1.5">
                <div class="flex items-center justify-between">
                    <p>Sous-total</p>
                    <p class="font-semibold" id="subtotalDisplay">0 F</p>
                </div>
                <div class="flex items-center justify-between">
                    <p>TVA ({{ $settings->tax_rate ?? 17.5 }}%)</p>
                    <p class="font-semibold" id="taxDisplay">0 F</p>
                </div>
                <div class="flex items-center justify-between text-sm font-bold text-slate-800 pt-1.5 border-t border-slate-200">
                    <p>Total TTC</p>
                    <p id="totalDisplay">0 F</p>
                </div>
            </div>
        </div>

        {{-- Document & Client Info --}}
        <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Type de document</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="devis">Devis</option>
                    <option value="proforma">Proforma</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Client <span class="text-red-400">*</span>
                </label>

                <input type="hidden" name="client_id" id="clientId" value="{{ old('client_id') }}">

                <input type="text" name="client_name" id="clientName" value="{{ old('client_name') }}"
                    placeholder="Nom du client pour recherche..."
                    class="w-full mb-2 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <input type="text" id="clientPhone" placeholder="Téléphone" readonly
                    class="w-full mb-2 px-3 py-2 border border-slate-100 bg-slate-50 text-slate-400 rounded-lg text-xs">

                <input type="text" id="clientType" placeholder="Tarification client" readonly
                    class="w-full mb-2 px-3 py-2 border border-slate-100 bg-slate-50 text-slate-400 rounded-lg text-xs">

                <div id="clientLookupBox" class="hidden mt-2 p-3 rounded-lg border border-amber-100 bg-amber-50"></div>

                <p id="clientSelectedText" class="hidden mt-2 text-xs font-medium text-emerald-600"></p>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Valide jusqu'au</label>
                <input type="date" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes / Conditions</label>
                <textarea name="notes" rows="2" placeholder="Ex: Livraison sous 24h..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 resize-none">{{ old('notes') }}</textarea>
            </div>

            <div id="hiddenItems"></div>

            <button type="submit"
                class="w-full py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                Enregistrer le document
            </button>
        </div>
    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
let cart = {};
let subtotal = 0;
const taxRate = parseFloat("{{ $settings->tax_rate ?? 17.5 }}");

function addProductToQuote(productId) {
    const btn = document.querySelector(`.product-card-btn[data-id="${productId}"]`);
    if (!btn) return;
    
    const name = btn.dataset.name;
    const stock = parseInt(btn.dataset.stock);
    const clientType = document.getElementById('clientType').value.toLowerCase();
    
    let price = parseFloat(btn.dataset.priceParticulier);
    if (clientType.includes('entreprise')) price = parseFloat(btn.dataset.priceEntreprise);
    else if (clientType.includes('revendeur')) price = parseFloat(btn.dataset.priceRevendeur);
    else if (clientType.includes('grossiste')) price = parseFloat(btn.dataset.priceGrossiste);

    if (cart[productId]) {
        cart[productId].qty++;
    } else {
        cart[productId] = {
            id: productId,
            name: name,
            price: price,
            qty: 1,
            stock: stock
        };
    }
    renderQuoteCart();
}

function recalculateQuotePrices() {
    const clientType = document.getElementById('clientType').value.toLowerCase();
    
    Object.keys(cart).forEach(id => {
        const btn = document.querySelector(`.product-card-btn[data-id="${id}"]`);
        if (btn) {
            let price = parseFloat(btn.dataset.priceParticulier);
            if (clientType.includes('entreprise')) price = parseFloat(btn.dataset.priceEntreprise);
            else if (clientType.includes('revendeur')) price = parseFloat(btn.dataset.priceRevendeur);
            else if (clientType.includes('grossiste')) price = parseFloat(btn.dataset.priceGrossiste);
            
            cart[id].price = price;
        }
    });
    
    renderQuoteCart();
}

function removeFromQuote(id) {
    delete cart[id];
    renderQuoteCart();
}

function updateQty(id, qty) {
    qty = parseInt(qty);
    if (qty <= 0) {
        removeFromQuote(id);
        return;
    }
    cart[id].qty = qty;
    renderQuoteCart();
}

function syncQuoteInputs() {
    const hidden = document.getElementById('hiddenItems');
    hidden.innerHTML = '';

    Object.entries(cart).forEach(([id, item], index) => {
        hidden.innerHTML += `
            <input type="hidden" name="items[${index}][product_id]" value="${id}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
        `;
    });
}

function renderQuoteCart() {
    const container = document.getElementById('cartItems');
    container.innerHTML = '';
    subtotal = 0;

    if (Object.keys(cart).length === 0) {
        container.innerHTML = '<p class="px-4 py-6 text-center text-xs text-slate-400">Document vide</p>';
    } else {
        Object.entries(cart).forEach(([id, item]) => {
            const rowSubtotal = item.price * item.qty;
            subtotal += rowSubtotal;

            container.innerHTML += `
                <div class="flex items-center gap-2 px-4 py-2.5">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-700 truncate">${item.name}</p>
                        <p class="text-xs text-slate-400">${Number(item.price).toLocaleString('fr-FR')} F x ${item.qty}</p>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="updateQty(${id}, ${item.qty - 1})"
                            class="w-5 h-5 rounded border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center text-xs">-</button>

                        <span class="text-xs font-semibold text-slate-700 w-6 text-center">${item.qty}</span>

                        <button type="button" onclick="updateQty(${id}, ${item.qty + 1})"
                            class="w-5 h-5 rounded border border-slate-200 text-slate-500 hover:bg-slate-100 flex items-center justify-center text-xs">+</button>

                        <button type="button" onclick="removeFromQuote(${id})"
                            class="w-5 h-5 rounded border border-red-100 text-red-400 hover:bg-red-50 flex items-center justify-center ml-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>`;
        });
    }

    const tax = (subtotal * taxRate) / 100;
    const total = subtotal + tax;

    document.getElementById('subtotalDisplay').textContent = subtotal.toLocaleString('fr-FR') + ' F';
    document.getElementById('taxDisplay').textContent = tax.toLocaleString('fr-FR') + ' F';
    document.getElementById('totalDisplay').textContent = total.toLocaleString('fr-FR') + ' F';

    syncQuoteInputs();
}

document.getElementById('searchProduct').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? 'flex' : 'none';
    });
});

let lookupTimer = null;
let selectedClientLocked = false;

const clientName = document.getElementById('clientName');
const clientPhone = document.getElementById('clientPhone');
const clientId = document.getElementById('clientId');
const clientType = document.getElementById('clientType');
const clientLookupBox = document.getElementById('clientLookupBox');
const clientSelectedText = document.getElementById('clientSelectedText');

function lookupClient() {
    if (selectedClientLocked) return;

    const name = clientName.value.trim();

    clientId.value = '';
    clientPhone.value = '';
    clientType.value = '';
    clientSelectedText.classList.add('hidden');
    clientSelectedText.textContent = '';

    if (name.length < 3) {
        clientLookupBox.classList.add('hidden');
        clientLookupBox.innerHTML = '';
        return;
    }

    clientLookupBox.classList.remove('hidden');
    clientLookupBox.innerHTML = `<p class="text-xs text-slate-500">Vérification du client...</p>`;

    fetch(`{{ route('clients.lookup') }}?name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.found) {
                clientLookupBox.innerHTML = `<p class="text-xs text-red-500 font-medium">Aucun client trouvé. Veuillez créer le client au préalable dans le module CRM.</p>`;
                return;
            }

            let html = `<p class="text-xs font-semibold text-slate-700 mb-2">Sélectionnez le client :</p>`;

            data.clients.forEach(client => {
                const safeName = String(client.name).replace(/'/g, "\\'");
                const safePhone = String(client.phone ?? '').replace(/'/g, "\\'");
                const typeLabel = client.type;
                const rawType = client.raw_type;

                html += `
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-slate-100 first:border-t-0">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">${client.name}</p>
                            <p class="text-xs text-slate-500">${client.phone ?? '—'} · ${typeLabel}</p>
                        </div>
                        <button type="button"
                            onclick="useExistingClient(${client.id}, '${safeName}', '${safePhone}', '${rawType}')"
                            class="px-2 py-1 bg-slate-900 text-white rounded text-[10px] font-bold">
                            Choisir
                        </button>
                    </div>
                `;
            });

            clientLookupBox.innerHTML = html;
        });
}

function useExistingClient(id, name, phone, type) {
    selectedClientLocked = true;

    clientId.value = id;
    clientName.value = name;
    clientPhone.value = phone;
    clientType.value = type.toUpperCase();

    clientLookupBox.classList.add('hidden');
    clientLookupBox.innerHTML = '';

    clientSelectedText.textContent = `Client sélectionné : ${name}`;
    clientSelectedText.classList.remove('hidden');

    recalculateQuotePrices();

    setTimeout(() => {
        selectedClientLocked = false;
    }, 800);
}

clientName.addEventListener('input', function () {
    selectedClientLocked = false;
    clearTimeout(lookupTimer);
    lookupTimer = setTimeout(lookupClient, 500);
});

document.getElementById('quoteForm').addEventListener('submit', function(e) {
    if (Object.keys(cart).length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins un produit.');
        return false;
    }
    if (!clientId.value) {
        e.preventDefault();
        alert('Veuillez sélectionner un client.');
        return false;
    }
});
</script>
@endpush
