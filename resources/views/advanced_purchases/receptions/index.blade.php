@extends('layouts.app')

@section('title', 'Réceptions de Livraisons')
@section('page-title', 'Réceptions de Livraisons')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Réceptions de Livraisons Fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $receptions->total() }} réception(s) enregistrée(s)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('advanced_purchases.orders.index') }}"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            Voir les Bons de Commande
        </a>
    </div>
</div>

{{-- Barre de filtres --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('advanced_purchases.receptions.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par référence, fournisseur, commande..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div>
            <button type="submit" class="w-full py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                Filtrer
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence Réception</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Bon de Commande</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date Réception</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Articles Réceptionnés</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($receptions as $reception)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $reception->reference }}</p>
                    <p class="text-[10px] text-slate-400">Reçu par {{ $reception->user->name ?? 'Système' }}</p>
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    @if($reception->supplierOrder)
                        <a href="{{ route('advanced_purchases.orders.show', $reception->supplier_order_id) }}" class="text-blue-600 hover:underline font-medium">
                            {{ $reception->supplierOrder->reference }}
                        </a>
                    @else
                        <span class="text-slate-400">N/A</span>
                    @endif
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $reception->supplierOrder->supplier->name ?? 'N/A' }}
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $reception->reception_date ? $reception->reception_date->format('d/m/Y') : 'N/A' }}
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700">
                        {{ $reception->items->sum('quantity') }} article(s) ({{ $reception->items->count() }} réf.)
                    </span>
                </td>

                <td class="px-5 py-3 text-right">
                    @if($reception->supplierOrder)
                        <a href="{{ route('advanced_purchases.orders.show', $reception->supplier_order_id) }}"
                            class="px-2.5 py-1 text-[11px] font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md transition">
                            Voir commande
                        </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">
                    Aucune réception enregistrée pour le moment.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($receptions->hasPages())
    <div class="px-5 py-3 border-t border-slate-100">
        {{ $receptions->links() }}
    </div>
    @endif
</div>

@endsection
