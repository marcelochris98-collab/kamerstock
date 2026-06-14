@extends('layouts.app')

@section('title', 'Rapports & Statistiques')
@section('page-title', 'Rapports Financiers & Activité')

@section('content')

{{-- Filtre de date --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col md:flex-row md:items-end gap-3">
        <div class="flex-1">
            <label class="block text-xs font-medium text-slate-500 mb-1">Date de début</label>
            <input type="date" name="start_date" value="{{ $startDate }}"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div class="flex-1">
            <label class="block text-xs font-medium text-slate-500 mb-1">Date de fin</label>
            <input type="date" name="end_date" value="{{ $endDate }}"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>
        <div>
            <button type="submit" class="w-full md:w-auto px-5 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                Mettre à jour
            </button>
        </div>
        <div>
            <a href="{{ route('reports.index') }}" class="w-full md:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg block text-center transition">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

{{-- Indicateurs Clés --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-slate-900">
        <p class="text-xs text-slate-400 font-semibold">Chiffre d'Affaires (CA)</p>
        <p class="text-lg font-bold text-slate-800 mt-1.5">{{ number_format($totalSales, 0, ',', ' ') }} FCFA</p>
        <p class="text-[10px] text-slate-400 mt-1">{{ $salesCount }} vente(s) enregistrée(s)</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-emerald-500">
        <p class="text-xs text-slate-400 font-semibold">Marge Commerciale</p>
        <p class="text-lg font-bold text-emerald-600 mt-1.5">{{ number_format($margin, 0, ',', ' ') }} FCFA</p>
        <div class="flex items-center gap-1 mt-1">
            <span class="text-[10px] font-bold bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded">
                Taux : {{ number_format($marginPercentage, 1, ',', ' ') }}%
            </span>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
        <p class="text-xs text-slate-400 font-semibold">Achats Fournisseurs</p>
        <p class="text-lg font-bold text-slate-800 mt-1.5">{{ number_format($totalPurchases, 0, ',', ' ') }} FCFA</p>
        <p class="text-[10px] text-slate-400 mt-1">Facturation de la période</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-amber-500">
        <p class="text-xs text-slate-400 font-semibold">Valorisation du Stock (Achat)</p>
        <p class="text-lg font-bold text-slate-800 mt-1.5">{{ number_format($stockValuationBuy, 0, ',', ' ') }} FCFA</p>
        <p class="text-[10px] text-slate-400 mt-1">Stock de vente estimé : {{ number_format($stockValuationSell, 0, ',', ' ') }} F</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    {{-- Graphique Chiffre d'Affaires Journalier --}}
    <div class="bg-white rounded-xl shadow-sm p-5 md:col-span-2">
        <h3 class="text-xs font-bold text-slate-800 mb-4">Évolution du Chiffre d'Affaires Journalier</h3>
        
        @if($dailySales->count() > 0)
        @php
            $maxVal = $dailySales->max('total') ?: 10000;
            $width = 500;
            $height = 150;
            $count = $dailySales->count();
            $barWidth = $count > 1 ? ($width / $count) * 0.7 : 40;
            $spacing = $count > 1 ? ($width / $count) * 0.3 : 10;
        @endphp
        <div class="relative w-full overflow-x-auto">
            <svg viewBox="0 0 550 180" class="w-full h-auto">
                {{-- Lignes horizontales de repère --}}
                <line x1="40" y1="10" x2="520" y2="10" stroke="#f1f5f9" stroke-width="1" />
                <line x1="40" y1="75" x2="520" y2="75" stroke="#f1f5f9" stroke-width="1" />
                <line x1="40" y1="140" x2="520" y2="140" stroke="#f1f5f9" stroke-width="1" />
                
                {{-- Libellés Y --}}
                <text x="5" y="15" fill="#94a3b8" font-size="8">{{ number_format($maxVal, 0, '', ' ') }}</text>
                <text x="5" y="80" fill="#94a3b8" font-size="8">{{ number_format($maxVal/2, 0, '', ' ') }}</text>
                <text x="5" y="145" fill="#94a3b8" font-size="8">0</text>

                {{-- Bâtons --}}
                @foreach($dailySales as $index => $day)
                    @php
                        $barHeight = ($day->total / $maxVal) * 130;
                        $x = 40 + $index * ($barWidth + $spacing);
                        $y = 140 - $barHeight;
                        $formattedDate = \Carbon\Carbon::parse($day->date)->format('d/m');
                    @endphp
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="2" fill="#0f172a" class="hover:fill-slate-600 transition duration-150">
                        <title>{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }} : {{ number_format($day->total, 0, ',', ' ') }} FCFA</title>
                    </rect>
                    
                    {{-- Afficher la date en bas sous le bâton si pas trop dense --}}
                    @if($count < 15 || $index % 3 == 0)
                    <text x="{{ $x + ($barWidth/2) }}" y="155" fill="#64748b" font-size="7" text-anchor="middle">
                        {{ $formattedDate }}
                    </text>
                    @endif
                @endforeach
            </svg>
        </div>
        @else
        <div class="flex flex-col items-center justify-center h-48 border border-dashed border-slate-100 rounded-xl">
            <p class="text-xs text-slate-400">Aucune donnée de vente pour cette période</p>
        </div>
        @endif
    </div>

    {{-- Ventilation par Mode de Paiement --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="text-xs font-bold text-slate-800 mb-4">Répartition des Ventes par Mode</h3>
        
        <div class="space-y-4">
            @forelse($paymentModes as $mode)
            @php
                $pct = $totalSales > 0 ? ($mode->total / $totalSales) * 100 : 0;
                $label = match($mode->payment_mode) {
                    'cash' => 'Espèces',
                    'orange_money' => 'Orange Money',
                    'mtn_money' => 'MTN Money',
                    'cheque' => 'Chèque',
                    'virement' => 'Virement',
                    'credit' => 'Crédit',
                    'mixte' => 'Mixte',
                    default => $mode->payment_mode
                };
                $color = match($mode->payment_mode) {
                    'cash' => 'bg-slate-900',
                    'orange_money' => 'bg-orange-500',
                    'mtn_money' => 'bg-yellow-400',
                    'credit' => 'bg-red-500',
                    'mixte' => 'bg-purple-600',
                    default => 'bg-slate-400'
                };
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1.5">
                    <span class="font-medium text-slate-700">{{ $label }}</span>
                    <span class="text-slate-500 font-semibold">{{ number_format($mode->total, 0, ',', ' ') }} F ({{ number_format($pct, 1) }}%)</span>
                </div>
                <div class="w-full bg-slate-50 rounded-full h-1.5">
                    <div class="{{ $color }} h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-12">Aucune vente enregistrée</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- Risques & Trésorerie --}}
    <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
        <h3 class="text-xs font-bold text-slate-800 border-b border-slate-50 pb-2">Créances & Dettes de Trésorerie</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-red-50/50 p-4 rounded-xl border border-red-100">
                <span class="text-[10px] text-red-500 font-bold uppercase">Créances Clients</span>
                <p class="text-base font-extrabold text-red-700 mt-1">{{ number_format($totalClientDebt, 0, ',', ' ') }} FCFA</p>
                <p class="text-[9px] text-red-400 mt-0.5">Montant cumulé des crédits en attente</p>
            </div>

            <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                <span class="text-[10px] text-amber-600 font-bold uppercase">Dettes Fournisseurs</span>
                <p class="text-base font-extrabold text-amber-700 mt-1">{{ number_format($totalSupplierDebt, 0, ',', ' ') }} FCFA</p>
                <p class="text-[9px] text-amber-400 mt-0.5">Factures fournisseurs non payées</p>
            </div>
        </div>
    </div>

    {{-- Statistiques de Rotation & Inventaire --}}
    <div class="bg-white rounded-xl shadow-sm p-5 space-y-4">
        <h3 class="text-xs font-bold text-slate-800 border-b border-slate-50 pb-2">Inventaire & Rotation de Stock</h3>
        
        <div class="space-y-3 text-xs">
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-400">Total d'articles en stock:</span>
                <span class="font-bold text-slate-700">{{ number_format($totalStockItems, 0, ',', ' ') }}</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-400">Valeur d'inventaire (Achat):</span>
                <span class="font-bold text-slate-700">{{ number_format($stockValuationBuy, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-400">Valeur d'inventaire (Vente):</span>
                <span class="font-bold text-slate-700">{{ number_format($stockValuationSell, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-slate-400">Marge brute latente estimée:</span>
                <span class="font-bold text-emerald-600">{{ number_format(max(0, $stockValuationSell - $stockValuationBuy), 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>
</div>

@endsection
