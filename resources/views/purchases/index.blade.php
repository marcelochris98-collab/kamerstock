@extends('layouts.app')

@section('title', 'Achats fournisseurs')
@section('page-title', 'Achats fournisseurs')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Achats fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $purchases->total() }} achat(s) enregistré(s)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('purchases.dashboard') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Dashboard achats
        </a>

        <a href="{{ route('purchases.create') }}"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            Nouvel achat
        </a>
        <a href="{{ route('purchases.debts') }}"
         class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
        Dettes fournisseurs
    </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Total</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Payé</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Reste</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Statut</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($purchases as $purchase)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $purchase->reference }}</p>
                    <p class="text-xs text-slate-400">{{ optional($purchase->purchase_date)->format('d/m/Y') }}</p>
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $purchase->supplier?->name ?? '—' }}
                </td>

                <td class="px-5 py-3 text-xs text-right font-semibold text-slate-800">
                    {{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-xs text-right text-emerald-600 font-semibold">
                    {{ number_format($purchase->amount_paid, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-xs text-right text-red-500 font-semibold">
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
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                        Voir
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun achat fournisseur enregistré</p>
                    <p class="text-xs text-slate-300 mt-1">Créez votre premier achat fournisseur</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($purchases->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $purchases->links() }}
    </div>
    @endif
</div>

@endsection