@extends('layouts.client-portal')

@section('title', 'Mes crédits')

@section('content')

<h1 class="text-lg font-bold text-slate-800 mb-5">Mes crédits</h1>

<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-5">
    <div class="px-4 py-3 border-b border-slate-100">
        <h2 class="text-xs font-bold text-slate-700">Crédits</h2>
    </div>

    @forelse($credits as $credit)
    <div class="px-4 py-4 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-800">Crédit #{{ $credit->id }}</p>
                <p class="text-xs text-slate-400">{{ $credit->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                {{ $credit->status === 'solde' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                {{ $credit->status }}
            </span>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-3">
            <div>
                <p class="text-xs text-slate-400">Total</p>
                <p class="text-xs font-bold text-slate-700">{{ number_format($credit->total_amount, 0, ',', ' ') }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Payé</p>
                <p class="text-xs font-bold text-emerald-600">{{ number_format($credit->amount_paid, 0, ',', ' ') }}</p>
            </div>

            <div>
                <p class="text-xs text-slate-400">Reste</p>
                <p class="text-xs font-bold text-red-500">{{ number_format($credit->amount_due, 0, ',', ' ') }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="px-4 py-10 text-center text-xs text-slate-400">
        Aucun crédit enregistré.
    </div>
    @endforelse

    @if($credits->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $credits->links() }}
    </div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100">
        <h2 class="text-xs font-bold text-slate-700">Derniers remboursements</h2>
    </div>

    @forelse($payments as $payment)
    <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-700">{{ $payment->payment_method }}</p>
            <p class="text-xs text-slate-400">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <p class="text-xs font-bold text-emerald-600">
            {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
        </p>
    </div>
    @empty
    <div class="px-4 py-8 text-center text-xs text-slate-400">
        Aucun remboursement.
    </div>
    @endforelse
</div>

@endsection