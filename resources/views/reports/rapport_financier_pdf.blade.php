<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Financier</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
        .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #0f172a; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; color: #0f172a; margin: 0; }
        .header .sub { font-size: 10px; color: #64748b; margin-top: 4px; }
        .header .doc-title { font-size: 13px; font-weight: bold; margin-top: 8px; color: #0f172a; text-transform: uppercase; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #fff; background: #0f172a; padding: 5px 10px; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table tr { border-bottom: 1px solid #f1f5f9; }
        table td { padding: 6px 10px; font-size: 10px; }
        table td.right { text-align: right; font-weight: bold; }
        table td.label { color: #64748b; }
        table tr.green td { background: #f0fdf4; color: #15803d; font-weight: bold; }
        table tr.red td { background: #fef2f2; color: #b91c1c; font-weight: bold; }
        table tr.dark td { background: #0f172a; color: #fff; font-weight: bold; }
        table tr.even { background: #f8fafc; }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ $settings->shop_name ?? 'KamerStock' }}</h1>
    @if(!empty($settings->address))<div class="sub">{{ $settings->address }}</div>@endif
    @if(!empty($settings->phone))<div class="sub">{{ $settings->phone }}</div>@endif
    <div class="doc-title">Rapport Financier Global</div>
    <div class="sub">
        Periode : du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        — Genere le {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

<div class="section">
    <div class="section-title">Chiffre d Affaires et Rentabilite</div>
    <table>
        <tr class="even"><td class="label">Chiffre d affaires (CA)</td><td class="right">{{ number_format($totalSales, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr><td class="label">Nombre de ventes</td><td class="right">{{ $salesCount }}</td></tr>
        <tr class="even"><td class="label">Ventes annulees</td><td class="right">{{ $cancelledCount }}</td></tr>
        <tr><td class="label">Total remises accordees</td><td class="right">{{ number_format($totalDiscount, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="even"><td class="label">Cout des ventes (COGS)</td><td class="right">{{ number_format($cogs, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="green"><td>Marge brute</td><td class="right">{{ number_format($margin, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="green"><td>Taux de marge</td><td class="right">{{ number_format($marginPercentage, 1, ',', ' ') }}%</td></tr>
        <tr class="dark">
            <td>Panier moyen par vente</td>
            <td class="right">{{ $salesCount > 0 ? number_format($totalSales / $salesCount, 0, ',', ' ') : '0' }} {{ $settings->currency ?? 'FCFA' }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Achats et Tresorerie</div>
    <table>
        <tr class="even"><td class="label">Total achats fournisseurs (periode)</td><td class="right">{{ number_format($totalPurchases, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="red"><td>Dettes fournisseurs (impayes cumules)</td><td class="right">{{ number_format($totalSupplierDebt, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="red"><td>Creances clients (credits en attente)</td><td class="right">{{ number_format($totalClientDebt, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="dark">
            <td>Solde net estime (CA - Achats - Dettes)</td>
            <td class="right">{{ number_format(max(0, $totalSales - $totalPurchases - $totalSupplierDebt), 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Valorisation du Stock</div>
    <table>
        <tr class="even"><td class="label">Nombre total d articles en stock</td><td class="right">{{ number_format($totalStockItems, 0, ',', ' ') }}</td></tr>
        <tr><td class="label">Valeur stock au prix d achat</td><td class="right">{{ number_format($stockValuationBuy, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="even"><td class="label">Valeur stock au prix de vente</td><td class="right">{{ number_format($stockValuationSell, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td></tr>
        <tr class="green">
            <td>Marge brute latente (stock)</td>
            <td class="right">{{ number_format(max(0, $stockValuationSell - $stockValuationBuy), 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
        </tr>
    </table>
</div>

@if($paymentModes->count() > 0)
<div class="section">
    <div class="section-title">Repartition par Mode de Paiement</div>
    <table>
        <tr style="background:#f8fafc;">
            <td style="font-weight:bold;">Mode</td>
            <td style="text-align:right; font-weight:bold;">Montant</td>
            <td style="text-align:right; font-weight:bold; width:80px;">Part</td>
        </tr>
        @foreach($paymentModes as $index => $mode)
        @php
            $pct = $totalSales > 0 ? ($mode->total / $totalSales) * 100 : 0;
            $label = match($mode->payment_mode) {
                'cash' => 'Especes',
                'orange_money' => 'Orange Money',
                'mtn_money' => 'MTN Money',
                'cheque' => 'Cheque',
                'virement' => 'Virement',
                'credit' => 'Credit',
                'mixte' => 'Mixte',
                default => ucfirst($mode->payment_mode)
            };
        @endphp
        <tr class="{{ $index % 2 === 0 ? 'even' : '' }}">
            <td class="label">{{ $label }}</td>
            <td style="text-align:right; font-weight:bold;">{{ number_format($mode->total, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
            <td style="text-align:right; color:#94a3b8;">{{ number_format($pct, 1) }}%</td>
        </tr>
        @endforeach
        <tr class="dark">
            <td>TOTAL</td>
            <td style="text-align:right;">{{ number_format($totalSales, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
            <td style="text-align:right;">100%</td>
        </tr>
    </table>
</div>
@endif

<div style="text-align:center; margin-top:30px; padding-top:10px; border-top:1px solid #e2e8f0; font-size:9px; color:#94a3b8;">
    {{ $settings->shop_name ?? 'KamerStock' }} — Rapport financier genere automatiquement — {{ now()->format('d/m/Y H:i') }}<br>
    Document confidentiel — Usage interne uniquement
</div>

</body>
</html>