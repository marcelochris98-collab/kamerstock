@extends('layouts.app')

@section('title', 'Détail crédit')
@section('page-title', 'Détail crédit')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    @foreach($errors->all() as $error)
    <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="mb-5">
    <a href="{{ route('credits.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
        ← Retour aux crédits
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-2">
        <p class="text-xs text-slate-400">Client</p>
        <h1 class="text-base font-semibold text-slate-800 mt-1">
            {{ $credit->client->name ?? 'Client supprimé' }}
        </h1>
        <p class="text-xs text-slate-400 mt-1">
            {{ $credit->client->phone ?? '—' }}
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400">Montant initial</p>
        <p class="text-lg font-bold text-slate-800 mt-1">
            {{ number_format($credit->total_amount, 0, ',', ' ') }} FCFA
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400">Reste à payer</p>
        <p class="text-lg font-bold text-red-600 mt-1">
            {{ number_format($credit->amount_due, 0, ',', ' ') }} FCFA
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Historique des paiements</h2>
        </div>

        <div class="p-5">
            @forelse($credit->payments as $payment)
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div>
                    <p class="text-xs font-semibold text-slate-700">
                        {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                    </p>
                    <p class="text-xs text-slate-400">
                        {{ $payment->payment_method }} — {{ $payment->created_at->format('d/m/Y H:i') }}
                    </p>
                  @if($payment->internal_reference)
<p class="text-xs text-slate-400">Réf interne : {{ $payment->internal_reference }}</p>
@endif

@if($payment->external_reference)
<p class="text-xs text-slate-400">Réf transaction : {{ $payment->external_reference }}</p>
@endif
                </div>


<div class="bg-white rounded-xl shadow-sm overflow-hidden mt-4">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="text-sm font-semibold text-slate-800">Historique du crédit</h2>
    </div>

    <div class="p-5">
        @forelse($credit->histories as $history)
        <div class="flex gap-3 pb-4 mb-4 border-b border-slate-50 last:border-0 last:mb-0 last:pb-0">
            <div class="w-3 h-3 rounded-full bg-slate-700 mt-1"></div>

            <div class="flex-1">
                <div class="flex justify-between gap-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $history->title }}</p>
                    <p class="text-xs text-slate-400">{{ $history->created_at->format('d/m/Y H:i') }}</p>
                </div>

                @if($history->description)
                <p class="text-xs text-slate-500 mt-1">{{ $history->description }}</p>
                @endif

                @if($history->amount > 0)
                <p class="text-xs font-semibold text-emerald-600 mt-1">
                    {{ number_format($history->amount, 0, ',', ' ') }} FCFA
                </p>
                @endif

                <p class="text-xs text-slate-400 mt-1">
                    Par : {{ $history->user->name ?? 'Système' }}
                </p>
            </div>
        </div>
        @empty
        <p class="text-xs text-slate-400">Aucun historique disponible.</p>
        @endforelse
    </div>
</div>




                <p class="text-xs text-slate-400">
                    {{ $payment->user->name ?? '—' }}
                </p>
            </div>
            @empty
            <p class="text-xs text-slate-400">Aucun paiement enregistré.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Enregistrer un remboursement</h2>
        </div>

        <div class="p-5">
            @if($credit->status === 'solde')
                <p class="text-xs text-emerald-600 font-semibold">Ce crédit est déjà soldé.</p>
            @else
                <form method="POST" action="{{ route('credits.payment', $credit) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Montant payé</label>
                        <input type="number" name="amount" min="1" max="{{ $credit->amount_due }}"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Mode de paiement</label>
                        <select name="payment_method"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                            <option value="cash">Espèces</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="mtn_money">MTN Money</option>
                            <option value="virement">Virement</option>
                        </select>
                    </div>

                 <div class="mb-3">
  <div class="mb-3">
    <label class="block text-xs font-medium text-slate-600 mb-1">Référence transaction</label>
    <input type="text" name="external_reference"
        placeholder="ID MoMo, ID Orange Money ou référence bancaire"
        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
    <p class="text-xs text-slate-400 mt-1">
        La référence interne sera générée automatiquement par le système.
    </p>
</div>
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Note</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                        Enregistrer paiement
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection
