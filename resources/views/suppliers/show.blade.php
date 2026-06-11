@extends('layouts.app')

@section('title', 'Fiche fournisseur')
@section('page-title', 'CRM fournisseur')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">{{ $supplier->name }}</h1>
        <p class="text-xs text-slate-400 mt-0.5">
            {{ $supplier->phone ?? 'Téléphone non renseigné' }} · {{ $supplier->email ?? 'Email non renseigné' }}
        </p>
    </div>

    <a href="{{ route('suppliers.index') }}"
        class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50">
        Retour
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Total achats</p>
        <p class="text-lg font-bold text-slate-800 mt-1">{{ number_format($totalPurchases, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Total payé</p>
        <p class="text-lg font-bold text-emerald-600 mt-1">{{ number_format($totalPaid, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Dette actuelle</p>
        <p class="text-lg font-bold text-red-500 mt-1">{{ number_format($totalDue, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Nombre achats</p>
        <p class="text-lg font-bold text-slate-800 mt-1">{{ $purchasesCount }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Statut</p>
        <p class="text-sm font-bold text-amber-600 mt-2 capitalize">{{ $supplierStatus }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h2 class="text-xs font-semibold text-slate-700 mb-4">Informations fournisseur</h2>

        <div class="space-y-3">
            <div>
                <p class="text-xs text-slate-400">Adresse</p>
                <p class="text-xs font-medium text-slate-700 mt-1">{{ $supplier->address ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Personne contact</p>
                <p class="text-xs font-medium text-slate-700 mt-1">{{ $supplier->contact_person ?? '—' }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Dernier achat</p>
                <p class="text-xs font-medium text-slate-700 mt-1">
                    {{ $lastPurchase ? $lastPurchase->created_at->format('d/m/Y H:i') : 'Aucun achat' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Notes</p>
                <p class="text-xs font-medium text-slate-700 mt-1">{{ $supplier->notes ?? '—' }}</p>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-xs font-semibold text-slate-700">Achats récents</h2>
            <a href="{{ route('purchases.create') }}" class="text-xs text-slate-500 hover:text-slate-800">Nouvel achat</a>
        </div>

        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Total</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Reste</th>
                    <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Statut</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($recentPurchases as $purchase)
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-5 py-3">
                        <p class="text-xs font-semibold text-slate-800">{{ $purchase->reference }}</p>
                        <p class="text-xs text-slate-400">{{ optional($purchase->purchase_date)->format('d/m/Y') }}</p>
                    </td>

                    <td class="px-5 py-3 text-xs text-right font-semibold text-slate-700">
                        {{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA
                    </td>

                    <td class="px-5 py-3 text-xs text-right font-semibold text-red-500">
                        {{ number_format($purchase->amount_due, 0, ',', ' ') }} FCFA
                    </td>

                    <td class="px-5 py-3 text-center">
                        @if($purchase->status === 'solde')
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Soldé</span>
                        @elseif($purchase->status === 'partiel')
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Partiel</span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">En attente</span>
                        @endif
                    </td>

                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('purchases.show', $purchase) }}"
                            class="text-xs text-slate-500 hover:text-slate-800">
                            Voir
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">Aucun achat enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-xs font-semibold text-slate-700">Paiements récents</h2>
        </div>

        <table class="w-full">
            <tbody>
                @forelse($recentPayments as $payment)
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-5 py-3">
                        <p class="text-xs font-semibold text-slate-700">{{ $payment->purchase?->reference ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                    </td>

                    <td class="px-5 py-3 text-xs text-slate-500">{{ $payment->payment_method }}</td>

                    <td class="px-5 py-3 text-xs text-right font-semibold text-emerald-600">
                        {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="px-5 py-8 text-center text-xs text-slate-400">Aucun paiement récent.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-xs font-semibold text-slate-700">Historique fournisseur</h2>
        </div>

        <div class="divide-y divide-slate-50">
            @forelse($histories as $history)
            <div class="px-5 py-3">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-700">{{ $history->title }}</p>
                    <span class="text-xs text-slate-400">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                </div>

                <p class="text-xs text-slate-500 mt-1">{{ $history->description }}</p>

                @if($history->amount)
                <p class="text-xs font-semibold text-slate-700 mt-1">
                    {{ number_format($history->amount, 0, ',', ' ') }} FCFA
                </p>
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center text-xs text-slate-400">
                Aucun historique fournisseur.
            </div>
            @endforelse
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="text-xs font-semibold text-slate-700">Produits liés au fournisseur</h2>
    </div>

    <table class="w-full">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix achat</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix vente</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Stock</th>
            </tr>
        </thead>

        <tbody>
            @forelse($supplier->products as $product)
            <tr class="border-b border-slate-50 last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $product->name }}</p>
                    <p class="text-xs text-slate-400">{{ $product->reference }}</p>
                </td>

                <td class="px-5 py-3 text-xs text-right text-slate-600">
                    {{ number_format($product->price_buy, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-xs text-right font-semibold text-slate-800">
                    {{ number_format($product->price_sell, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $product->quantity <= $product->alert_threshold ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $product->quantity }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-8 text-center text-xs text-slate-400">
                    Aucun produit lié à ce fournisseur.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection