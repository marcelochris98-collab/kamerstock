@extends('layouts.app')

@section('title', 'Détail achat')
@section('page-title', 'Détail achat fournisseur')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    @foreach($errors->all() as $error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Achat {{ $purchase->reference }}</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $purchase->supplier?->name ?? '—' }}</p>
    </div>

    <a href="{{ route('purchases.index') }}"
        class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50">
        Retour
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Total achat</p>
        <p class="text-lg font-bold text-slate-800 mt-1">{{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Montant payé</p>
        <p class="text-lg font-bold text-emerald-600 mt-1">{{ number_format($purchase->amount_paid, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Reste à payer</p>
        <p class="text-lg font-bold text-red-500 mt-1">{{ number_format($purchase->amount_due, 0, ',', ' ') }} FCFA</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-xs font-semibold text-slate-700">Produits achetés</h2>
        </div>

        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Qté</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-5 py-3 text-xs font-semibold text-slate-700">{{ $item->product?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-center text-slate-600">{{ $item->quantity }}</td>
                    <td class="px-5 py-3 text-xs text-right text-slate-600">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                    <td class="px-5 py-3 text-xs text-right font-semibold text-slate-800">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="text-xs font-semibold text-slate-700 mb-4">Paiement fournisseur</h2>

        @if($purchase->amount_due > 0)
        <form method="POST" action="{{ route('purchases.payment', $purchase) }}">
            @csrf

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Montant</label>
                <input type="number" name="amount" max="{{ $purchase->amount_due }}" min="1"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Mode paiement</label>
                <select name="payment_method" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    <option value="cash">Espèces</option>
                    <option value="orange_money">Orange Money</option>
                    <option value="mtn_money">MTN Money</option>
                    <option value="virement">Virement</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Référence externe</label>
                <input type="text" name="external_reference"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs"></textarea>
            </div>

            <button class="w-full px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg">
                Enregistrer paiement
            </button>
        </form>
        @else
            <div class="px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-semibold">
                Cet achat est totalement soldé.
            </div>
        @endif
    </div>
</div>

<div class="mt-5 bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="text-xs font-semibold text-slate-700">Historique des paiements</h2>
    </div>

    <table class="w-full">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Méthode</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchase->payments as $payment)
            <tr class="border-b border-slate-50 last:border-0">
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->payment_method }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->internal_reference ?? $payment->reference ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-right font-semibold text-emerald-600">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-8 text-center text-xs text-slate-400">Aucun paiement enregistré</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection