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
