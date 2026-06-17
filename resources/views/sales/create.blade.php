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
                    onclick="addProductToCart({{ $product->id }})"
                    class="flex items-start gap-3 p-3 border border-slate-100 rounded-xl hover:border-slate-300 hover:bg-slate-50 transition text-left product-card product-card-btn"
                    data-search="{{ strtolower($product->name) }}">
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
    <div class="lg:col-span-1 space-y-4">

        {{-- Panier --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-50">
                <p class="text-xs font-semibold text-slate-800">Panier</p>
            </div>

            <div id="cartItems" class="divide-y divide-slate-50 min-h-16">
                <p class="px-4 py-6 text-center text-xs text-slate-400">Panier vide</p>
            </div>

            <div class="px-4 py-2 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs text-slate-500 hidden" id="discountRow">
                <p>Réduction Fidélité</p>
                <p id="discountDisplay">-0 F</p>
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
                    class="w-full mb-2 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="particulier">Particulier</option>
                    <option value="entreprise">Entreprise</option>
                    <option value="revendeur">Revendeur</option>
                    <option value="grossiste">Grossiste</option>
                </select>

                <!-- Points de Fidélité -->
                <div id="loyaltySection" class="hidden mb-2 p-3 bg-amber-50 rounded-lg border border-amber-100">
                    <p class="text-xs font-semibold text-amber-800">Programme de fidélité</p>
                    <p class="text-[10px] text-amber-700 mt-0.5">
                        Solde : <span id="loyaltyBalance" class="font-bold">0</span> points (<span id="loyaltyDiscountValue" class="font-bold">0</span> FCFA de réduction)
                    </p>
                    
                    <div class="flex items-center gap-2 mt-2">
                        <input type="checkbox" name="use_loyalty" id="useLoyalty" value="1" onchange="toggleLoyaltyRedemption()" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                        <label for="useLoyalty" class="text-[10px] font-medium text-slate-700">Utiliser les points pour cette vente</label>
                    </div>
                    
                    <input type="hidden" name="redeem_points" id="redeemPoints" value="0">
                </div>

                <div id="clientLookupBox" class="hidden mt-2 p-3 rounded-lg border border-amber-100 bg-amber-50"></div>

                <p id="clientSelectedText" class="hidden mt-2 text-xs font-medium text-emerald-600"></p>
            </div>

            {{-- Paiement --}}
            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Mode de paiement</label>

                <div class="grid grid-cols-2 gap-2">
                    @foreach(['cash' => 'Espèces', 'orange_money' => 'Orange Money', 'mtn_money' => 'MTN Money', 'credit' => 'Crédit', 'mixte' => 'Mixte'] as $val => $label)
                    <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition payment-option" data-value="{{ $val }}">
                        <input type="radio" name="payment_mode" value="{{ $val }}" class="hidden" {{ $val === 'cash' ? 'checked' : '' }}>
                        <span class="text-xs text-slate-600">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div id="mixteSection" class="hidden mb-3 p-3 bg-slate-50 rounded-lg border border-slate-100 space-y-2">
                <p class="text-xs font-semibold text-slate-700">Détails Paiement Mixte</p>
                <div>
                    <label class="block text-[10px] font-medium text-slate-500">Espèces (FCFA)</label>
                    <input type="number" name="payments[cash]" id="mixte_cash" value="0" min="0" class="w-full px-2 py-1 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 mixte-input">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-slate-500">Orange Money (FCFA)</label>
                    <input type="number" name="payments[orange_money]" id="mixte_orange" value="0" min="0" class="w-full px-2 py-1 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 mixte-input">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-slate-500">MTN Money (FCFA)</label>
                    <input type="number" name="payments[mtn_money]" id="mixte_mtn" value="0" min="0" class="w-full px-2 py-1 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 mixte-input">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-slate-500">Virement (FCFA)</label>
                    <input type="number" name="payments[virement]" id="mixte_virement" value="0" min="0" class="w-full px-2 py-1 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 mixte-input">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-slate-500">Chèque (FCFA)</label>
                    <input type="number" name="payments[cheque]" id="mixte_cheque" value="0" min="0" class="w-full px-2 py-1 border border-slate-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 mixte-input">
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

function addProductToCart(productId) {
    const btn = document.querySelector(`.product-card-btn[data-id="${productId}"]`);
    if (!btn) return;
    
    const name = btn.dataset.name;
    const stock = parseInt(btn.dataset.stock);
    const clientType = document.getElementById('clientType').value;
    
    let price = parseFloat(btn.dataset.priceParticulier);
    if (clientType === 'entreprise') price = parseFloat(btn.dataset.priceEntreprise);
    else if (clientType === 'revendeur') price = parseFloat(btn.dataset.priceRevendeur);
    else if (clientType === 'grossiste') price = parseFloat(btn.dataset.priceGrossiste);

    if (cart[productId]) {
        if (cart[productId].qty >= stock) return;
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
    renderCart();
}

function recalculateCartPrices() {
    const clientType = document.getElementById('clientType').value;
    
    Object.keys(cart).forEach(id => {
        const btn = document.querySelector(`.product-card-btn[data-id="${id}"]`);
        if (btn) {
            let price = parseFloat(btn.dataset.priceParticulier);
            if (clientType === 'entreprise') price = parseFloat(btn.dataset.priceEntreprise);
            else if (clientType === 'revendeur') price = parseFloat(btn.dataset.priceRevendeur);
            else if (clientType === 'grossiste') price = parseFloat(btn.dataset.priceGrossiste);
            
            cart[id].price = price;
        }
    });
    
    renderCart();
}

function toggleLoyaltyRedemption() {
    const useLoyalty = document.getElementById('useLoyalty').checked;
    const loyaltySec = document.getElementById('loyaltySection');
    const redeemPointsInput = document.getElementById('redeemPoints');
    
    if (useLoyalty) {
        const maxPoints = parseInt(loyaltySec.dataset.maxPoints) || 0;
        const maxDiscountAllowed = total;
        const pointsNeededForTotal = Math.ceil(maxDiscountAllowed / 10);
        const pointsToRedeem = Math.min(maxPoints, pointsNeededForTotal);
        
        redeemPointsInput.value = pointsToRedeem;
    } else {
        redeemPointsInput.value = 0;
    }
    
    renderCart();
}

document.getElementById('clientType').addEventListener('change', recalculateCartPrices);

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

    const discountRow = document.getElementById('discountRow');
    const discountDisplay = document.getElementById('discountDisplay');
    const redeemPoints = parseInt(document.getElementById('redeemPoints').value) || 0;
    const loyaltyDiscount = redeemPoints * 10;

    if (loyaltyDiscount > 0) {
        discountRow.classList.remove('hidden');
        discountDisplay.textContent = '-' + loyaltyDiscount.toLocaleString('fr-FR') + ' F';
    } else {
        discountRow.classList.add('hidden');
    }

    const netTotal = Math.max(0, total - loyaltyDiscount);
    document.getElementById('totalDisplay').textContent = netTotal.toLocaleString('fr-FR') + ' F';

    syncCartInputs();
    updateChange();
}

function updateChange() {
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const redeemPoints = parseInt(document.getElementById('redeemPoints').value) || 0;
    const loyaltyDiscount = redeemPoints * 10;
    const netTotal = Math.max(0, total - loyaltyDiscount);
    const change = paid - netTotal;
    const el = document.getElementById('changeDisplay');

    el.textContent = Math.max(0, change).toLocaleString('fr-FR') + ' F';
    el.className = change >= 0
        ? 'text-xs font-semibold text-emerald-600'
        : 'text-xs font-semibold text-red-500';
}

document.getElementById('amountPaid').addEventListener('input', updateChange);

function updateMixteTotal() {
    let sum = 0;
    document.querySelectorAll('.mixte-input').forEach(input => {
        sum += parseFloat(input.value) || 0;
    });
    document.getElementById('amountPaid').value = sum;
    updateChange();
}

document.querySelectorAll('.mixte-input').forEach(input => {
    input.addEventListener('input', updateMixteTotal);
});

document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => {
            o.classList.remove('border-slate-900', 'bg-slate-50');
        });

        this.classList.add('border-slate-900', 'bg-slate-50');
        this.querySelector('input').checked = true;

        const mode = this.dataset.value;
        const amountSection = document.getElementById('amountSection');
        const mixteSection = document.getElementById('mixteSection');
        const amountPaidInput = document.getElementById('amountPaid');

        if (mode === 'credit') {
            amountSection.style.display = 'none';
            mixteSection.classList.add('hidden');
            amountPaidInput.value = 0;
            amountPaidInput.readOnly = false;
        } else if (mode === 'mixte') {
            amountSection.style.display = 'block';
            mixteSection.classList.remove('hidden');
            amountPaidInput.readOnly = true;
            updateMixteTotal();
        } else {
            amountSection.style.display = 'block';
            mixteSection.classList.add('hidden');
            amountPaidInput.readOnly = false;
        }
        updateChange();
    });
});

document.querySelector('.payment-option[data-value="cash"]').classList.add('border-slate-900', 'bg-slate-50');

document.getElementById('searchProduct').addEventListener('input', function() {
    const q = this.value.toLowerCase();

    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = card.dataset.search.includes(q) ? 'flex' : 'none';
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
    
    // Reset loyalty
    document.getElementById('loyaltySection').classList.add('hidden');
    document.getElementById('useLoyalty').checked = false;
    document.getElementById('redeemPoints').value = 0;
    renderCart();
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
                const rawType = client.raw_type;
                const points = client.loyalty_points;

                html += `
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-amber-100 first:border-t-0">
                        <div>
                            <p class="text-xs font-semibold text-slate-800">${client.name}</p>
                            <p class="text-xs text-slate-500">${client.phone ?? ''} — ${client.type}</p>
                            <p class="text-xs text-slate-500">
                                Points de fidélité : ${points} pts — Crédit dispo : ${Number(client.credit_available).toLocaleString('fr-FR')} FCFA
                            </p>
                        </div>

                        <button type="button"
                            onclick="useExistingClient(${client.id}, '${safeName}', '${safePhone}', '${rawType}', ${points})"
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

function useExistingClient(id, name, phone, type, loyaltyPoints) {
    selectedClientLocked = true;

    clientId.value = id;
    clientName.value = name;
    clientPhone.value = phone;

    const typeSelect = document.getElementById('clientType');
    typeSelect.value = type;

    const loyaltySec = document.getElementById('loyaltySection');
    const balanceSpan = document.getElementById('loyaltyBalance');
    const discountSpan = document.getElementById('loyaltyDiscountValue');

    if (loyaltyPoints > 0) {
        loyaltySec.classList.remove('hidden');
        balanceSpan.textContent = loyaltyPoints;
        discountSpan.textContent = (loyaltyPoints * 10).toLocaleString('fr-FR');
        loyaltySec.dataset.maxPoints = loyaltyPoints;
    } else {
        loyaltySec.classList.add('hidden');
        document.getElementById('useLoyalty').checked = false;
        document.getElementById('redeemPoints').value = 0;
    }

    recalculateCartPrices();

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
