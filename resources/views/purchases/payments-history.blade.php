@extends('layouts.app')

@section('title', 'Paiements fournisseurs')
@section('page-title', 'Historique paiements fournisseurs')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Historique paiements fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $payments->total() }} paiement(s)</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Achat</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Méthode</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant</th>
            </tr>
        </thead>

        <tbody>
            @forelse($payments as $payment)
            <tr class="border-b border-slate-50 hover:bg-slate-50 last:border-0">
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->supplier?->name ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->purchase?->reference ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->payment_method }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $payment->internal_reference ?? $payment->reference ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-right font-semibold text-emerald-600">
                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center text-xs text-slate-400">
                    Aucun paiement fournisseur enregistré.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($payments->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $payments->links() }}
    </div>
    @endif
</div>

@endsection