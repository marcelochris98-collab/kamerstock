@extends('layouts.app')

@section('title', 'Dettes fournisseurs')
@section('page-title', 'Dettes fournisseurs')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Dettes fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $purchases->total() }} dette(s) fournisseur en cours</p>
    </div>

    <a href="{{ route('purchases.index') }}"
        class="px-4 py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50">
        Tous les achats
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Achat</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Total</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Payé</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Dette</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($purchases as $purchase)
            <tr class="border-b border-slate-50 hover:bg-slate-50 last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $purchase->reference }}</p>
                    <p class="text-xs text-slate-400">{{ optional($purchase->purchase_date)->format('d/m/Y') }}</p>
                </td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $purchase->supplier?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-right text-slate-600">{{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA</td>
                <td class="px-5 py-3 text-xs text-right text-emerald-600">{{ number_format($purchase->amount_paid, 0, ',', ' ') }} FCFA</td>
                <td class="px-5 py-3 text-xs text-right font-bold text-red-500">{{ number_format($purchase->amount_due, 0, ',', ' ') }} FCFA</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('purchases.show', $purchase) }}"
                        class="inline-flex px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50">
                        Régler
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center text-xs text-slate-400">
                    Aucune dette fournisseur en cours.
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