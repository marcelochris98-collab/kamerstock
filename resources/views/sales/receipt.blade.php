@extends('layouts.app')

@section('title', 'Recu #' . str_pad($sale->id, 4, '0', STR_PAD_LEFT))
@section('page-title', 'Recu de vente')

@section('content')

<div class="max-w-lg mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('sales.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-sm font-semibold text-slate-800">Recu #{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</h1>
        <button onclick="window.print()"
            class="ml-auto flex items-center gap-2 px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6" id="receipt">
        {{-- En-tete --}}
        <div class="text-center mb-6 pb-5 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-800">{{ $settings->shop_name ?? 'KamerStock' }}</h2>
            @if($settings?->address)
            <p class="text-xs text-slate-400 mt-0.5">{{ $settings->address }}</p>
            @endif
            @if($settings?->phone)
            <p class="text-xs text-slate-400">{{ $settings->phone }}</p>
            @endif
            <div class="mt-3">
                <p class="text-sm font-bold text-slate-700">RECU DE VENTE</p>
                <p class="text-xs text-slate-400">#{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }} — {{ $sale->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Infos vente --}}
        <div class="grid grid-cols-2 gap-3 mb-5 text-xs">
            <div>
                <p class="text-slate-400 mb-0.5">Client</p>
                <p class="font-medium text-slate-700">{{ $sale->client?->name ?? 'Passager' }}</p>
            </div>
            <div>
                <p class="text-slate-400 mb-0.5">Caissier</p>
                <p class="font-medium text-slate-700">{{ $sale->user->name }}</p>
            </div>
            <div>
                <p class="text-slate-400 mb-0.5">Paiement</p>
                <p class="font-medium text-slate-700">{{ $sale->payment_mode_label }}</p>
            </div>
            <div>
                <p class="text-slate-400 mb-0.5">Statut</p>
                <span class="px-2 py-0.5 rounded text-xs font-medium
                    {{ $sale->status === 'completee' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $sale->status_label }}
                </span>
            </div>
        </div>

        {{-- Articles --}}
        <table class="w-full mb-5">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="pb-2 text-left text-xs font-medium text-slate-400">Article</th>
                    <th class="pb-2 text-center text-xs font-medium text-slate-400">Qte</th>
                    <th class="pb-2 text-right text-xs font-medium text-slate-400">P.U</th>
                    <th class="pb-2 text-right text-xs font-medium text-slate-400">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $detail)
                <tr class="border-b border-slate-50">
                    <td class="py-2 text-xs text-slate-700">{{ $detail->product->name }}</td>
                    <td class="py-2 text-center text-xs text-slate-600">{{ $detail->quantity }}</td>
                    <td class="py-2 text-right text-xs text-slate-600">{{ number_format($detail->unit_price, 0, ',', ' ') }}</td>
                    <td class="py-2 text-right text-xs font-medium text-slate-700">{{ number_format($detail->subtotal, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totaux --}}
        <div class="border-t border-slate-100 pt-4 space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <p class="text-slate-500">Sous-total</p>
                <p class="font-medium text-slate-700">{{ number_format($sale->total_amount, 0, ',', ' ') }} {{ $settings->currency ?? 'XAF' }}</p>
            </div>
            @if($sale->payments->count() > 0)
            <div class="border-t border-slate-50 py-1.5 space-y-1 mb-2">
                <p class="text-[10px] font-semibold text-slate-400">Règlements :</p>
                @foreach($sale->payments as $payment)
                <div class="flex items-center justify-between text-xs pl-2">
                    <p class="text-slate-500">{{ $payment->payment_mode_label }}</p>
                    <p class="font-medium text-slate-700">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $settings->currency ?? 'XAF' }}</p>
                </div>
                @endforeach
            </div>
            @endif

            @if($sale->status !== 'credit')
            <div class="flex items-center justify-between text-xs">
                <p class="text-slate-500">Montant reçu</p>
                <p class="font-medium text-slate-700">{{ number_format($sale->amount_paid, 0, ',', ' ') }} {{ $settings->currency ?? 'XAF' }}</p>
            </div>
            <div class="flex items-center justify-between text-xs">
                <p class="text-slate-500">Monnaie rendue</p>
                <p class="font-medium text-slate-700">{{ number_format($sale->change_due, 0, ',', ' ') }} {{ $settings->currency ?? 'XAF' }}</p>
            </div>
            @endif
            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <p class="text-sm font-bold text-slate-800">TOTAL</p>
                <p class="text-sm font-bold text-slate-800">{{ number_format($sale->total_amount, 0, ',', ' ') }} {{ $settings->currency ?? 'XAF' }}</p>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">Merci pour votre achat</p>
    </div>

    @if(auth()->user()->hasPermission('sales.create'))
    <div class="mt-4 text-center">
        <a href="{{ route('sales.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Nouvelle vente
        </a>
    </div>
    @endif
</div>

@endsection
