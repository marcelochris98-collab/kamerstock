@extends('layouts.app')

@section('title', 'Nouvelle vente')
@section('page-title', 'Point de Vente')

@section('content')

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    <p class="text-xs font-semibold text-red-600 mb-1">Erreurs détectées :</p>
    @foreach($errors->all() as $error)
    <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('sales.store') }}" id="saleForm">
@csrf

<div class="grid grid-cols-3 gap-5">

    {{-- Produits disponibles --}}
    <div class="col-span-2">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50 flex items-center gap-3">
                <input type="text" id="searchProduct" placeholder="Rechercher un produit..."
                    class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="grid grid-cols-2 gap-3 p-4 max-h-96 overflow-y-auto" id="productGrid">
                @foreach($products as $product)
                <button type="button"
                    onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price_sell }}, {{ $product->quantity }})"
                    class="flex items-start gap-3 p-3 border border-slate-100 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition text-left product-card"
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

    {{-- Panier + Paiement --}}
    <div class="col-span-1 space-y-4">

        {{-- Panier --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-50">
                <p class="text-xs font-semibold text-slate-800">Panier</p>
            </div>

            <div id="cartItems" class="divide-y divide-slate-50 min-h-16">
                <p class="px-4 py-6 text-center text-xs text-slate-400">Panier vide</p>
            </div>

            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-700">Total</p>
                    <p class="text-sm font-bold text-slate-800" id="totalDisplay">0 F</p>
                </div>
            </div>
        </div>

        {{-- Client --}}
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Client <span class="text-red-400">*</span>
                </label>

                <input type="hidden" name="client_id" id="clientId" value="{{ old('client_id') }}">

                <input type="text" name="client_name" id="clientName" value="{{ old('client_name') }}"
                    placeholder="Nom du client"
                    class="w-full mb-2 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <input type="text" name="client_phone" id="clientPhone" value="{{ old('client_phone') }}"
                    placeholder="Téléphone"
                    class="w-full mb-2 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <input type="email" name="client_email" id="clientEmail" value="{{ old('client_email') }}"
                    placeholder="Email facultatif"
                    class="w-full mb-2 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <select name="client_type" id="clientType"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="particulier">Particulier</option>
                    <option value="entreprise">Entreprise</option>
                    <option value="revendeur">Revendeur</option>
                </select>

                <div id="clientLookupBox" class="hidden mt-2 p-3 rounded-lg border border-amber-100 bg-amber-50"></div>

                <p id="clientSelectedText" class="hidden mt-2 text-xs font-medium text-emerald-600"></p>
            </div>

            {{-- Paiement --}}
            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Mode de paiement</label>

                <div class="grid grid-cols-2 gap-2">
                    @foreach(['cash' => 'Espèces', 'orange_money' => 'Orange Money', 'mtn_money' => 'MTN Money', 'credit' => 'Crédit'] as $val => $label)
                    <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition payment-option" data-value="{{ $val }}">
                        <input type="radio" name="payment_mode" value="{{ $val }}" class="hidden" {{ $val === 'cash' ? 'checked' : '' }}>
                        <span class="text-xs text-slate-600">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="amountSection" class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Montant reçu (FCFA)</label>

                <input type="number" name="amount_paid" id="amountPaid" value="{{ old('amount_paid', 0) }}" min="0"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">

                <div class="flex items-center justify-between mt-1.5">
                    <p class="text-xs text-slate-400">Monnaie à rendre</p>
                    <p class="text-xs font-semibold text-emerald-600" id="changeDisplay">0 F</p>
                </div>
            </div>

            {{-- Champs cachés du panier --}}
            <div id="hiddenItems"></div>

            <button type="submit"
                class="w-full py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                Valider la vente
            </button>
        </div>
    </div>
</div>

</form>

@endsection

@push('scripts')
<script>
let cart = {};
let total = 0;

function addToCart(id, name, price, stock) {
    if (cart[id]) {
        if (cart[id].qty >= stock) return;
        cart[id].qty++;
    } else {
        cart[id] = {
            id: id,
            name: name,
            price: price,
            qty: 1,
            stock: stock
        };
    }

    renderCart();
}

function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

function updateQty(id, qty) {
    qty = parseInt(qty);

    if (qty <= 0) {
        removeFromCart(id);
        return;
    }

    if (qty > cart[id].stock) {
        qty = cart[id].stock;
    }

    cart[id].qty = qty;
    renderCart();
}

function syncCartInputs() {
    const hidden = document.getElementById('hiddenItems');

    hidden.innerHTML = '';

    Object.entries(cart).forEach(([id, item], index) => {
        hidden.innerHTML += `
            <input type="hidden" name="items[${index}][product_id]" value="${id}">
            <input type="hidden" name="items[${index}][quantity]" value="${item.qty}">
        `;
    });
}

function renderCart() {
    const container = document.getElementById('cartItems');

    container.innerHTML = '';
    total = 0;

    if (Object.keys(cart).length === 0) {
        container.innerHTML = '<p class="px-4 py-6 text-center text-xs text-slate-400">Panier vide</p>';
    } else {
        Object.entries(cart).forEach(([id, item]) => {
            const subtotal = item.price * item.qty;
            total += subtotal;

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

                        <button type="button" onclick="removeFromCart(${id})"
                            class="w-5 h-5 rounded border border-red-100 text-red-400 hover:bg-red-50 flex items-center justify-center ml-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>`;
        });
    }

    document.getElementById('totalDisplay').textContent = total.toLocaleString('fr-FR') + ' F';

    syncCartInputs();
    updateChange();
}

function updateChange() {
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = paid - total;
    const el = document.getElementById('changeDisplay');

    el.textContent = Math.max(0, change).toLocaleString('fr-FR') + ' F';
    el.className = change >= 0
        ? 'text-xs font-semibold text-emerald-600'
        : 'text-xs font-semibold text-red-500';
}

document.getElementById('amountPaid').addEventListener('input', updateChange);

document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => {
            o.classList.remove('border-slate-900', 'bg-slate-50');
        });

        this.classList.add('border-slate-900', 'bg-slate-50');
        this.querySelector('input').checked = true;

        const mode = this.dataset.value;
        const amountSection = document.getElementById('amountSection');

        amountSection.style.display = mode === 'credit' ? 'none' : 'block';
    });
});

document.querySelector('.payment-option[data-value="cash"]').classList.add('border-slate-900', 'bg-slate-50');

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
const clientLookupBox = document.getElementById('clientLookupBox');
const clientSelectedText = document.getElementById('clientSelectedText');

function clearClientSelection() {
    if (selectedClientLocked) {
        return;
    }

    clientId.value = '';
    clientSelectedText.classList.add('hidden');
    clientSelectedText.textContent = '';
}

function lookupClient() {
    if (selectedClientLocked) {
        return;
    }

    const name = clientName.value.trim();
    const phone = clientPhone.value.trim();

    clientId.value = '';
    clientSelectedText.classList.add('hidden');
    clientSelectedText.textContent = '';

    if (phone.length < 6 && name.length < 3) {
        clientLookupBox.classList.add('hidden');
        clientLookupBox.innerHTML = '';
        return;
    }

    clientLookupBox.classList.remove('hidden');
    clientLookupBox.innerHTML = `
        <p class="text-xs text-slate-500">Vérification du client...</p>
    `;

    fetch(`{{ route('clients.lookup') }}?phone=${encodeURIComponent(phone)}&name=${encodeURIComponent(name)}`)
        .then(response => response.json())
        .then(data => {
            if (!data.found) {
                clientLookupBox.classList.remove('hidden');
                clientLookupBox.innerHTML = `
                    <p class="text-xs font-semibold text-emerald-700">Nouveau client</p>
                    <p class="text-xs text-emerald-600 mt-1">
                        Aucun client existant trouvé. Il sera créé automatiquement lors de la validation de la vente.
                    </p>
                `;
                return;
            }

            let html = `
                <p class="text-xs font-semibold text-amber-700 mb-2">
                    Client déjà enregistré ou similaire trouvé :
                </p>
            `;

            data.clients.forEach(client => {
                const safeName = String(client.name).replace(/'/g, "\\'");
                const safePhone = String(client.phone ?? '').replace(/'/g, "\\'");

                html += `
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-amber-100 first:border-t-0">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">${client.name}</p>
                            <p class="text-xs text-slate-500">${client.phone ?? ''} — ${client.type}</p>
                            <p class="text-xs text-slate-500">
                                Score : ${client.score}/100 — Crédit dispo : ${Number(client.credit_available).toLocaleString('fr-FR')} FCFA
                            </p>
                        </div>

                        <button type="button"
                            onclick="useExistingClient(${client.id}, '${safeName}', '${safePhone}')"
                            class="px-2 py-1 bg-slate-900 text-white rounded text-xs font-semibold">
                            Utiliser
                        </button>
                    </div>
                `;
            });

            html += `
                <p class="text-xs text-amber-600 mt-2">
                    Si ce n'est pas le même client, continuez la saisie normalement : un nouveau client sera créé.
                </p>
            `;

            clientLookupBox.innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur lookup client :', error);

            clientLookupBox.classList.remove('hidden');
            clientLookupBox.innerHTML = `
                <p class="text-xs font-semibold text-red-600">Erreur de vérification client</p>
                <p class="text-xs text-red-500 mt-1">Impossible de vérifier si ce client existe déjà.</p>
            `;
        });
}

function useExistingClient(id, name, phone) {
    selectedClientLocked = true;

    clientId.value = id;
    clientName.value = name;
    clientPhone.value = phone;

    clientLookupBox.classList.add('hidden');
    clientLookupBox.innerHTML = '';

    clientSelectedText.textContent = `Client existant sélectionné : ${name}`;
    clientSelectedText.classList.remove('hidden');

    setTimeout(() => {
        selectedClientLocked = false;
    }, 800);
}

[clientName, clientPhone].forEach(input => {
    input.addEventListener('input', function () {
        selectedClientLocked = false;
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(lookupClient, 500);
    });

    input.addEventListener('blur', function () {
        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(lookupClient, 200);
    });
});

document.getElementById('saleForm').addEventListener('submit', function(e) {
    syncCartInputs();

    if (Object.keys(cart).length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins un produit au panier.');
        return false;
    }

    if (!clientId.value && (clientName.value.trim() === '' || clientPhone.value.trim() === '')) {
        e.preventDefault();
        alert('Veuillez identifier le client avant de valider la vente.');
        return false;
    }
});
</script>
@endpush
