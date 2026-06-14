@extends('layouts.app')

@section('title', 'Crédits clients')
@section('page-title', 'Crédits clients')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Liste des crédits clients</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $credits->total() }} crédit(s) actif(s)</p>
    </div>

    <div>
        <a href="{{ route('export', 'credits') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Exporter CSV
        </a>
    </div>
</div>

{{-- Filtres --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('credits.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher client, téléphone..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div>
            <select name="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous les statuts actifs</option>
                <option value="en_attente" {{ request('status') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                <option value="partiel" {{ request('status') === 'partiel' ? 'selected' : '' }}>Partiel</option>
                <option value="en_retard" {{ request('status') === 'en_retard' ? 'selected' : '' }}>En retard</option>
                <option value="solde" {{ request('status') === 'solde' ? 'selected' : '' }}>Soldé</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                Filtrer
            </button>
            @if(request('search') || request('status'))
            <a href="{{ route('credits.index') }}" class="py-2 px-3 border border-slate-200 text-slate-500 hover:text-slate-800 text-xs font-semibold rounded-lg hover:bg-slate-50 transition text-center flex items-center justify-center">
                Reset
            </a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Client</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Montant initial</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Payé</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Reste</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Statut</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($credits as $credit)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $credit->client->name ?? 'Client supprimé' }}</p>
                    <p class="text-xs text-slate-400">{{ $credit->created_at->format('d/m/Y H:i') }}</p>
                </td>

                <td class="px-5 py-3 text-right text-xs font-semibold text-slate-700">
                    {{ number_format($credit->total_amount, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-right text-xs font-semibold text-emerald-600">
                    {{ number_format($credit->amount_paid, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-right text-xs font-semibold text-red-600">
                    {{ number_format($credit->amount_due, 0, ',', ' ') }} FCFA
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold
                        {{ $credit->status === 'partiel' ? 'bg-amber-50 text-amber-700' :
                           ($credit->status === 'en_retard' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ str_replace('_', ' ', ucfirst($credit->status)) }}
                    </span>
                </td>

                <td class="px-5 py-3 text-right">
                    <a href="{{ route('credits.show', $credit) }}"
                        class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                        Voir
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun crédit actif</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($credits->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $credits->links() }}
    </div>
    @endif
</div>

@endsection
