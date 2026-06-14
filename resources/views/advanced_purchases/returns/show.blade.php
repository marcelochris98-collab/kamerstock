@extends('layouts.app')

@section('title', 'Détails du Retour Fournisseur ' . $return->reference)
@section('page-title', 'Retour Fournisseur ' . $return->reference)

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="max-w-4xl mx-auto space-y-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('advanced_purchases.returns.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            ←
        </a>
        <div>
            <h1 class="text-sm font-semibold text-slate-800">Retour {{ $return->reference }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Enregistré le {{ $return->return_date->format('d/m/Y') }} par {{ $return->user->name }}</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-5">
        <div class="col-span-1 bg-white rounded-xl shadow-sm p-4 h-fit space-y-3">
            <h3 class="text-xs font-bold text-slate-700 border-b border-slate-50 pb-2">Informations Générales</h3>
            
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Fournisseur:</span>
                    <span class="font-semibold text-slate-800">{{ $return->supplier->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Facture d'achat:</span>
                    @if($return->purchase)
                        <a href="{{ route('purchases.show', $return->purchase_id) }}" class="font-semibold text-blue-600 hover:underline">
                            {{ $return->purchase->reference }}
                        </a>
                    @else
                        <span class="text-slate-400">Aucune (Générique)</span>
                    @endif
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Statut:</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                        {{ $return->status_label }}
                    </span>
                </div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold text-slate-800">
                    <span>Total Retourné:</span>
                    <span>{{ number_format($return->total_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            @if($return->reason)
            <div class="mt-4 p-3 bg-slate-50 rounded text-[11px] text-slate-600">
                <span class="font-semibold block mb-0.5">Raison du retour:</span>
                {{ $return->reason }}
            </div>
            @endif
        </div>

        <div class="col-span-2 bg-white rounded-xl shadow-sm overflow-hidden h-fit">
            <div class="px-5 py-4 border-b border-slate-50">
                <h3 class="text-xs font-bold text-slate-800">Articles retournés</h3>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Quantité</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix d'Achat</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($return->items as $item)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition last:border-0">
                        <td class="px-5 py-3">
                            <p class="text-xs font-semibold text-slate-800">{{ $item->product->name }}</p>
                            <p class="text-[10px] text-slate-400">Réf : {{ $item->product->reference }}</p>
                        </td>
                        <td class="px-5 py-3 text-center text-xs text-slate-700 font-medium">
                            -{{ $item->quantity }} {{ $item->product->unit }}
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-slate-600">
                            {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-3 text-right text-xs font-semibold text-slate-850">
                            {{ number_format($item->subtotal, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
