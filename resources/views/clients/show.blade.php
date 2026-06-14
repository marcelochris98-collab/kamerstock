@extends('layouts.app')

@section('title', 'Fiche client')
@section('page-title', 'Fiche client intelligente')

@section('content')

<div class="mb-5">
    <a href="{{ route('clients.index') }}" class="text-xs text-slate-500 hover:text-slate-800">
        ← Retour aux clients
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-1">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-lg font-bold text-slate-600">
                {{ strtoupper(substr($client->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-sm font-semibold text-slate-800 truncate">{{ $client->name }}</h1>
                <p class="text-xs text-slate-400">{{ $client->type_label }}</p>
                <p class="text-[11px] text-slate-400 truncate">{{ $client->phone ?? '—' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 lg:col-span-1">
        <p class="text-xs text-slate-400 mb-1">Points Fidélité</p>
        <div class="flex items-end gap-1">
            <span class="text-2xl font-bold text-amber-500">{{ $client->loyalty_points ?? 0 }}</span>
            <span class="text-xs text-slate-400 mb-1">points</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-1">Valeur de réduction : {{ number_format(($client->loyalty_points ?? 0) * 10, 0, ',', ' ') }} F</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400 mb-1">Score fidélité</p>
        <div class="flex items-end gap-2">
            <span class="text-2xl font-bold text-slate-800">{{ $client->loyalty_score }}</span>
            <span class="text-xs text-slate-400 mb-1">/100</span>
        </div>
        <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full bg-slate-900" style="width: {{ $client->loyalty_score }}%"></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400 mb-2">Statut client</p>
        <span class="px-3 py-1 rounded-lg text-xs font-semibold
            {{ $client->loyalty_status === 'premium' ? 'bg-amber-50 text-amber-700' :
               ($client->loyalty_status === 'fidele' ? 'bg-emerald-50 text-emerald-700' :
               ($client->loyalty_status === 'regulier' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600')) }}">
            {{ $client->loyalty_status_label }}
        </span>

        <p class="text-xs text-slate-400 mt-4 mb-1">Risque crédit</p>
        <span class="px-3 py-1 rounded-lg text-xs font-semibold
            {{ $client->risk_level === 'faible' ? 'bg-emerald-50 text-emerald-700' :
               ($client->risk_level === 'eleve' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
            {{ $client->risk_level_label }}
        </span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400">Crédit recommandé</p>
        <p class="text-lg font-bold text-slate-800 mt-1">
            {{ number_format($client->recommended_credit_limit, 0, ',', ' ') }} FCFA
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400">Crédit utilisé</p>
        <p class="text-lg font-bold text-red-600 mt-1">
            {{ number_format($client->credit_used, 0, ',', ' ') }} FCFA
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-slate-400">Crédit disponible</p>
        <p class="text-lg font-bold text-emerald-600 mt-1">
            {{ number_format($client->credit_available, 0, ',', ' ') }} FCFA
        </p>
    </div>
</div>

<!-- IA Kamerstock Panel -->
<div class="bg-white rounded-xl shadow-sm p-5 mb-5 border border-slate-100">
    <div class="flex items-center gap-2 mb-4">
        <div class="p-1 bg-slate-900 rounded-lg text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h2 class="text-sm font-semibold text-slate-800">Intelligence Artificielle Kamerstock</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Credit Risk Assessment -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Évaluation du Risque de Crédit</h3>
            <div class="flex items-center gap-4 mb-3">
                <span class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl font-bold 
                    {{ $creditRisk['risk_rating'] === 'A' ? 'bg-emerald-100 text-emerald-800' :
                       ($creditRisk['risk_rating'] === 'B' ? 'bg-blue-100 text-blue-800' :
                       ($creditRisk['risk_rating'] === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')) }}">
                    {{ $creditRisk['risk_rating'] }}
                </span>
                <div>
                    <p class="text-xs font-semibold text-slate-800">
                        {{ $creditRisk['risk_rating'] === 'A' ? 'Risque Très Faible (Excellent)' :
                           ($creditRisk['risk_rating'] === 'B' ? 'Risque Faible (Bon)' :
                           ($creditRisk['risk_rating'] === 'C' ? 'Risque Modéré (Moyen)' : 'Risque Élevé (Surveiller)')) }}
                    </p>
                    <p class="text-xs text-slate-400">Taux de remboursement : {{ $creditRisk['repayment_rate'] }}%</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 bg-white p-3 rounded-lg border border-slate-100 mt-2">
                {{ $creditRisk['explanation'] }}
            </p>
            <div class="mt-2 text-[10px] text-slate-400 flex items-center justify-between">
                <span>Dette active : {{ number_format($creditRisk['total_due'], 0, ',', ' ') }} FCFA</span>
                <span>Source : {{ $creditRisk['source'] }}</span>
            </div>
        </div>

        <!-- Product Recommendations -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
            <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Produits Recommandés</h3>
            <div class="space-y-2">
                @forelse($recommendations['recommendations'] as $prod)
                    <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-100">
                        <div>
                            <p class="text-xs font-medium text-slate-800">{{ $prod['name'] }}</p>
                            <p class="text-[10px] text-slate-400">Réf : {{ $prod['reference'] }}</p>
                        </div>
                        <span class="text-xs font-bold text-slate-900">{{ number_format($prod['price'], 0, ',', ' ') }} FCFA</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center font-medium">Aucune recommandation disponible pour le moment.</p>
                @endforelse
            </div>
            <p class="text-[10px] text-slate-400 mt-3 text-right">Source : {{ $recommendations['source'] }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Dernières ventes</h2>
        </div>

        <div class="p-5">
            @forelse($client->sales as $sale)
                <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Vente #{{ $sale->id }}</p>
                        <p class="text-xs text-slate-400">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="text-xs font-bold text-slate-800">
                        {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA
                    </p>
                </div>
            @empty
                <p class="text-xs text-slate-400">Aucune vente enregistrée.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Crédits récents</h2>
        </div>

        <div class="p-5">
            @forelse($client->creditSales as $credit)
                <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Crédit #{{ $credit->id }}</p>
                        <p class="text-xs text-slate-400">Statut : {{ $credit->status }}</p>
                    </div>
                    <p class="text-xs font-bold text-red-600">
                        {{ number_format($credit->amount_due, 0, ',', ' ') }} FCFA
                    </p>
                </div>
            @empty
                <p class="text-xs text-slate-400">Aucun crédit en cours.</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
