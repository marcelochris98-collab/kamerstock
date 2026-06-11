@extends('layouts.client-portal')

@section('title', 'Mes achats')

@section('content')

<h1 class="text-lg font-bold text-slate-800 mb-5">Mes achats</h1>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @forelse($sales as $sale)
    <div class="px-4 py-4 border-b border-slate-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-800">Vente #{{ $sale->id }}</p>
                <p class="text-xs text-slate-400">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <p class="text-sm font-bold text-slate-800">
                {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA
            </p>
        </div>

        <div class="mt-3 space-y-1">
            @foreach($sale->details as $detail)
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>{{ $detail->product?->name ?? 'Produit supprimé' }} × {{ $detail->quantity }}</span>
                <span>{{ number_format($detail->subtotal, 0, ',', ' ') }} FCFA</span>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="px-4 py-10 text-center text-xs text-slate-400">
        Aucun achat enregistré.
    </div>
    @endforelse

    @if($sales->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $sales->links() }}
    </div>
    @endif
</div>

@endsection