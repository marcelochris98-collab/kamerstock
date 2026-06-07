@extends('layouts.app')

@section('title', 'Historique des remboursements')
@section('page-title', 'Historique des remboursements')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Historique des remboursements crédit</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $payments->total() }} paiement(s) enregistré(s)</p>
    </div>

    <a href="{{ route('credits.index') }}"
        class="px-3 py-2 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
        Retour aux crédits
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Client</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Crédit</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant payé</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Mode</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Caissier</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Date</th>
            </tr>
        </thead>

        <tbody>
            @forelse($payments as $payment)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">
                        {{ $payment->client->name ?? 'Client supprimé' }}
                    </p>
                    <p class="text-xs text-slate-400">
                        {{ $payment->client->phone ?? '—' }}
                    </p>
                </td>

                <td class="px-5 py-3">
                    <a href="{{ route('credits.show', $payment->credit_sale_id) }}"
                        class="text-xs font-semibold text-slate-700 hover:text-amber-500">
                        Crédit #{{ $payment->credit_sale_id }}
                    </a>
                </td>

                <td class="px-5 py-3 text-right text-xs font-bold text-emerald-600">
                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600">
                        {{ str_replace('_', ' ', ucfirst($payment->payment_method)) }}
                    </span>
                </td>

                <td class="px-5 py-3">
                    @if($payment->internal_reference)
                    <p class="text-xs text-slate-700">
                        Interne : {{ $payment->internal_reference }}
                    </p>
                    @endif

                    @if($payment->external_reference)
                    <p class="text-xs text-slate-400">
                        Transaction : {{ $payment->external_reference }}
                    </p>
                    @else
                    <p class="text-xs text-slate-300">—</p>
                    @endif
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $payment->user->name ?? '—' }}
                </td>

                <td class="px-5 py-3 text-right">
                    <p class="text-xs text-slate-700">{{ $payment->created_at->format('d/m/Y') }}</p>
                    <p class="text-xs text-slate-400">{{ $payment->created_at->format('H:i') }}</p>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun remboursement enregistré</p>
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
