@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Tableau de bord')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-base font-semibold text-slate-800"> {{ auth()->user()->name }}</h2>
        <p class="text-xs text-slate-400 mt-0.5">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>
    @if(auth()->user()->hasPermission('sales.create'))
    <a href="{{ route('sales.create') }}"
        class="flex items-center gap-2 px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle vente
    </a>
    @endif
</div>

{{-- STATS --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-400 font-medium">CA du jour</span>
            <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-xl font-bold text-slate-800 tracking-tight">{{ number_format($caJour, 0, ',', ' ') }}</p>
        <p class="text-xs text-slate-400 mt-1">FCFA encaissés</p>
        <div class="mt-3 h-0.5 bg-slate-100 rounded">
            <div class="h-0.5 bg-amber-400 rounded" style="width: 65%"></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-400 font-medium">Ventes</span>
            <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <p class="text-xl font-bold text-slate-800 tracking-tight">{{ $ventesJour }}</p>
        <p class="text-xs text-slate-400 mt-1">transactions aujourd'hui</p>
        <div class="mt-3 h-0.5 bg-slate-100 rounded">
            <div class="h-0.5 bg-blue-400 rounded" style="width: 40%"></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sml p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-400 font-medium">Produits</span>
            <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
        <p class="text-xl font-bold text-slate-800 tracking-tight">{{ $totalProduits }}</p>
        <p class="text-xs text-slate-400 mt-1">références actives</p>
        <div class="mt-3 h-0.5 bg-slate-100 rounded">
            <div class="h-0.5 bg-emerald-400 rounded" style="width: 80%"></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-slate-400 font-medium">Alertes stock</span>
            <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
        <p class="text-xl font-bold {{ $alertesStock > 0 ? 'text-red-600' : 'text-slate-800' }} tracking-tight">{{ $alertesStock }}</p>
        <p class="text-xs text-slate-400 mt-1">produit(s) en rupture</p>
        <div class="mt-3 h-0.5 bg-slate-100 rounded">
            <div class="h-0.5 bg-red-400 rounded" style="width: {{ $alertesStock > 0 ? '20' : '0' }}%"></div>
        </div>
    </div>
</div>

{{-- GRAPHE + TOP PRODUITS --}}
<div class="grid grid-cols-3 gap-4 mb-4">

    {{-- Graphe ventes --}}
    <div class="col-span-2 bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-50">
            <div>
                <p class="text-xs font-semibold text-slate-700">Ventes — 7 derniers jours</p>
                <p class="text-xs mt-0.5" id="trendLabel"></p>
            </div>
            <span class="text-xs text-slate-400">Cette semaine</span>
        </div>
        <div class="p-4">
            <div style="height:120px; position:relative">
                <svg id="salesChart" viewBox="0 0 500 100" preserveAspectRatio="none" style="width:100%;height:100%;overflow:visible"></svg>
            </div>
            <div class="flex justify-between mt-2">
                @foreach($labels as $label)
                <span class="text-xs text-slate-400">{{ $label }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Top produits --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-50">
            <p class="text-xs font-semibold text-slate-700">Top produits</p>
            <span class="text-xs text-slate-400">Ce mois</span>
        </div>
        <div>
            @forelse($topProduits as $i => $prod)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-50 last:border-0">
                <div class="w-5 h-5 rounded flex items-center justify-center text-xs font-bold flex-shrink-0
                    {{ $i === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $i + 1 }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-slate-700 truncate">{{ $prod->name }}</p>
                    <p class="text-xs text-slate-400">{{ $prod->total_qty }} unités</p>
                </div>
                <p class="text-xs font-semibold text-slate-700">{{ number_format($prod->total_ca, 0, ',', ' ') }} F</p>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-xs text-slate-400">Aucune vente ce mois</div>
            @endforelse
        </div>
    </div>
</div>

{{-- DERNIÈRES VENTES + ALERTES --}}
<div class="grid grid-cols-3 gap-4">

    {{-- Dernières ventes --}}
    <div class="col-span-2 bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-50">
            <p class="text-xs font-semibold text-slate-700">Dernières ventes</p>
            @if(auth()->user()->hasPermission('sales.view'))
            <a href="{{ route('sales.index') }}" class="text-xs text-slate-400 hover:text-slate-600">Voir tout</a>
            @endif
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-50">
                    <th class="px-4 py-2 text-left text-xs text-slate-400 font-medium">N°</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 font-medium">Client</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 font-medium">Montant</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 font-medium">Paiement</th>
                    <th class="px-4 py-2 text-left text-xs text-slate-400 font-medium">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dernieresVentes as $sale)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                    <td class="px-4 py-2.5 text-xs text-slate-400 font-mono">#{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 py-2.5 text-xs font-medium text-slate-700">
                        {{ $sale->client?->name ?? 'Passager' }}
                    </td>
                    <td class="px-4 py-2.5 text-xs font-semibold text-slate-800">
                        {{ number_format($sale->total_amount, 0, ',', ' ') }} F
                    </td>
                    <td class="px-4 py-2.5 text-xs text-slate-400">{{ $sale->payment_mode_label }}</td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $sale->status === 'completee' ? 'bg-emerald-50 text-emerald-700' :
                               ($sale->status === 'credit' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                            {{ $sale->status_label }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-xs text-slate-400">Aucune vente enregistrée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Alertes stock --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-50">
            <p class="text-xs font-semibold text-slate-700">Alertes stock</p>
            <span class="text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded">{{ $alertesStock }}</span>
        </div>
        <div>
            @forelse($produitsAlerte as $produit)
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-50 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-slate-700 truncate">{{ $produit->name }}</p>
                    <p class="text-xs text-slate-400">Seuil : {{ $produit->alert_threshold }}</p>
                </div>
                <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded ml-2">
                    {{ $produit->quantity }}
                </span>
            </div>
            @empty
            <div class="px-4 py-6 text-center text-xs text-slate-400">Aucune alerte</div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const data   = @json($totals);
const labels = @json($labels);
const W = 500, H = 100, pad = 10;
const chartW = W - pad*2, chartH = H - pad*2;
const n      = data.length;
const maxVal = Math.max(...data, 1);
const getX   = (i) => pad + (i / (n-1)) * chartW;
const getY   = (v) => pad + chartH - (v / maxVal) * chartH;
const svg    = document.getElementById('salesChart');

// Zone
const area = document.createElementNS('http://www.w3.org/2000/svg','polygon');
const pts  = data.map((v,i) => `${getX(i)},${getY(v)}`).join(' ');
area.setAttribute('points', `${getX(0)},${pad+chartH} ${pts} ${getX(n-1)},${pad+chartH}`);
area.setAttribute('fill','rgba(245,158,11,0.08)');
svg.appendChild(area);

// Ligne moyenne
const avg = data.reduce((a,b)=>a+b,0)/n;
const ref = document.createElementNS('http://www.w3.org/2000/svg','line');
ref.setAttribute('x1',pad); ref.setAttribute('x2',W-pad);
ref.setAttribute('y1',getY(avg)); ref.setAttribute('y2',getY(avg));
ref.setAttribute('stroke','#e2e8f0'); ref.setAttribute('stroke-dasharray','3,3'); ref.setAttribute('stroke-width','1');
svg.appendChild(ref);

// Courbe
const path = document.createElementNS('http://www.w3.org/2000/svg','path');
path.setAttribute('d', data.map((v,i)=>`${i===0?'M':'L'}${getX(i)},${getY(v)}`).join(' '));
path.setAttribute('stroke','#f59e0b'); path.setAttribute('stroke-width','2');
path.setAttribute('fill','none'); path.setAttribute('stroke-linecap','round'); path.setAttribute('stroke-linejoin','round');
svg.appendChild(path);

// Points
data.forEach((v,i) => {
    const c = document.createElementNS('http://www.w3.org/2000/svg','circle');
    c.setAttribute('cx',getX(i)); c.setAttribute('cy',getY(v));
    c.setAttribute('r', i===n-1?'4':'2.5');
    c.setAttribute('fill', i===n-1?'#f59e0b':'white');
    c.setAttribute('stroke','#f59e0b'); c.setAttribute('stroke-width','2');
    svg.appendChild(c);
});

// Tendance
const trend = data[n-1] > data[0];
document.getElementById('trendLabel').innerHTML = trend
    ? '<span style="color:#16a34a;font-size:10px;font-weight:600">Tendance haussiere</span>'
    : '<span style="color:#94a3b8;font-size:10px">Pas encore de donnees</span>';
</script>
@endpush
