@extends('layouts.app')

@section('title', 'Retours Fournisseurs')
@section('page-title', 'Retours Fournisseurs')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Retours Fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $returns->total() }} retour(s) enregistré(s)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('advanced_purchases.returns.create') }}"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            Enregistrer un Retour
        </a>
    </div>
</div>

{{-- Barre de filtres --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('advanced_purchases.returns.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par référence, fournisseur..."
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
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Référence</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Facture d'Origine</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date du Retour</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant Total</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Statut</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($returns as $ret)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $ret->reference }}</p>
                    <p class="text-[10px] text-slate-400">Par {{ $ret->user->name }}</p>
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $ret->supplier->name }}
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    @if($ret->purchase)
                        <a href="{{ route('purchases.show', $ret->purchase_id) }}" class="text-blue-600 hover:underline">
                            {{ $ret->purchase->reference }}
                        </a>
                    @else
                        <span class="text-slate-400">Générique</span>
                    @endif
                </td>

                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $ret->return_date->format('d/m/Y') }}
                </td>

                <td class="px-5 py-3 text-xs text-right font-semibold text-slate-800">
                    {{ number_format($ret->total_amount, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">Validé</span>
                </td>

                <td class="px-5 py-3 text-right">
                    <a href="{{ route('advanced_purchases.returns.show', $ret) }}"
                        class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                        Voir
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun retour fournisseur enregistré</p>
                    <p class="text-xs text-slate-300 mt-1">Créez votre premier retour fournisseur</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($returns->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $returns->links() }}
    </div>
    @endif
</div>

@endsection
