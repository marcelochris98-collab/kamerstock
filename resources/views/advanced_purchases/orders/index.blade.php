@extends('layouts.app')

@section('title', 'Bons de Commande Fournisseurs')
@section('page-title', 'Bons de Commande')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Bons de Commande Fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $orders->total() }} commande(s) enregistrée(s)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('advanced_purchases.suggestions') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Suggestions de stock
        </a>

        <a href="{{ route('advanced_purchases.orders.create') }}"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            Créer un Bon de Commande
        </a>
    </div>
</div>

{{-- Barre de filtres --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('advanced_purchases.orders.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par référence, fournisseur..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div>
            <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous les statuts</option>
                <option value="commande" {{ request('status') === 'commande' ? 'selected' : '' }}>Commandé</option>
                <option value="recu_partiel" {{ request('status') === 'recu_partiel' ? 'selected' : '' }}>Reçu Partiel</option>
                <option value="recu_complet" {{ request('status') === 'recu_complet' ? 'selected' : '' }}>Reçu Complet</option>
                <option value="annule" {{ request('status') === 'annule' ? 'selected' : '' }}>Annulé</option>
            </select>
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
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date de Commande</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant Total</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Statut</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($orders as $order)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $order->reference }}</p>
                    <p class="text-[10px] text-slate-400">Créé par {{ $order->user->name }}</p>
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $order->supplier->name }}
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $order->order_date->format('d/m/Y') }}
                </td>

                <td class="px-5 py-3 text-xs text-right font-semibold text-slate-800">
                    {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-center">
                    @if($order->status === 'commande')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700">Commandé</span>
                    @elseif($order->status === 'recu_partiel')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">Reçu Partiel</span>
                    @elseif($order->status === 'recu_complet')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">Reçu Complet</span>
                    @elseif($order->status === 'annule')
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-red-50 text-red-600">Annulé</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-50 text-slate-600">{{ $order->status }}</span>
                    @endif
                </td>

                <td class="px-5 py-3 text-right">
                    <a href="{{ route('advanced_purchases.orders.show', $order) }}"
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                        Gérer / Voir
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun bon de commande enregistré</p>
                    <p class="text-xs text-slate-300 mt-1">Créez votre premier bon de commande fournisseur</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($orders->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection
