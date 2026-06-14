@extends('layouts.app')

@section('title', 'Détails du document')
@section('page-title', 'Détails du document')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-semibold text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="mb-5 flex items-center justify-between">
    <a href="{{ route('quotes.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
        ← Retour aux documents
    </a>

    <div class="flex items-center gap-2">
        <a href="{{ route('quotes.print', $quote->id) }}" target="_blank"
            class="px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Imprimer / PDF
        </a>

        @if($quote->status !== 'converti')
        <form method="POST" action="{{ route('quotes.convert', $quote->id) }}" onsubmit="return confirm('Convertir ce devis en vente et décrémenter le stock ?')">
            @csrf
            <button type="submit" class="px-4 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                Facturer (Créer vente)
            </button>
        </form>
        @else
        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-100">
            Facturé (Vente #{{ $quote->converted_sale_id }})
        </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-3 gap-5">
    <!-- Fiche principale -->
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase">{{ $quote->type_label }}</h2>
                    <p class="text-xs text-slate-400">Réf : {{ $quote->reference }}</p>
                </div>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase
                    {{ $quote->status === 'converti' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                    {{ $quote->status_label }}
                </span>
            </div>

            <!-- Articles -->
            <table class="w-full mb-6 text-xs text-slate-700">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400">
                        <th class="pb-2 text-left font-medium">Description</th>
                        <th class="pb-2 text-center font-medium">Qté</th>
                        <th class="pb-2 text-right font-medium">Prix unitaire</th>
                        <th class="pb-2 text-right font-medium">Total HT</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($quote->details as $detail)
                    <tr>
                        <td class="py-3 text-slate-800">
                            <p class="font-semibold">{{ $detail->product->name }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $detail->product->reference }}</p>
                        </td>
                        <td class="py-3 text-center">{{ $detail->quantity }}</td>
                        <td class="py-3 text-right">{{ number_format($detail->unit_price, 0, ',', ' ') }} F</td>
                        <td class="py-3 text-right font-semibold">{{ number_format($detail->subtotal, 0, ',', ' ') }} F</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Notes -->
            @if($quote->notes)
            <div class="bg-slate-50 rounded-lg p-3 text-xs text-slate-600 border border-slate-100">
                <p class="font-semibold text-slate-700 mb-1">Notes / Conditions :</p>
                <p class="whitespace-pre-line leading-relaxed">{{ $quote->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Totaux & Client -->
    <div class="col-span-1 space-y-4">
        <!-- Client card -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 space-y-3">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Client</h3>
            
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-600">
                    {{ strtoupper(substr($quote->client->name ?? 'P', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ $quote->client->name ?? 'Passager' }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $quote->client->type_label ?? 'Particulier' }}</p>
                </div>
            </div>

            <div class="text-[11px] text-slate-600 space-y-1 pt-2 border-t border-slate-50">
                <p><strong>Téléphone :</strong> {{ $quote->client->phone ?? '—' }}</p>
                <p><strong>Email :</strong> {{ $quote->client->email ?? '—' }}</p>
                <p><strong>Adresse :</strong> {{ $quote->client->address ?? '—' }}</p>
            </div>
        </div>

        <!-- Totaux Card -->
        <div class="bg-white rounded-xl shadow-sm p-4 border border-slate-100 space-y-3">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Totaux du document</h3>

            <div class="text-xs text-slate-600 space-y-2">
                <div class="flex items-center justify-between">
                    <p>Sous-total HT</p>
                    <p class="font-semibold text-slate-800">{{ number_format($quote->subtotal, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="flex items-center justify-between">
                    <p>TVA ({{ $settings->tax_rate ?? 17.5 }}%)</p>
                    <p class="font-semibold text-slate-800">{{ number_format($quote->tax_amount, 0, ',', ' ') }} FCFA</p>
                </div>
                @if($quote->discount_amount > 0)
                <div class="flex items-center justify-between text-emerald-600">
                    <p>Réduction</p>
                    <p class="font-semibold">-{{ number_format($quote->discount_amount, 0, ',', ' ') }} FCFA</p>
                </div>
                @endif
                <div class="flex items-center justify-between text-sm font-bold text-slate-800 pt-2 border-t border-slate-200">
                    <p>Net à payer</p>
                    <p>{{ number_format($quote->total_amount, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>

            <div class="text-[10px] text-slate-400 pt-2 border-t border-slate-50 space-y-1">
                <p><strong>Créé par :</strong> {{ $quote->user->name }}</p>
                @if($quote->valid_until)
                <p><strong>Date d'échéance :</strong> {{ $quote->valid_until->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
