<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Ventes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
        h1 { font-size: 18px; font-weight: bold; color: #0f172a; margin: 0; text-align: center; }
        .sub { font-size: 10px; color: #64748b; margin-top: 4px; text-align: center; }
        .doc-title { font-size: 13px; font-weight: bold; margin-top: 8px; color: #0f172a; text-transform: uppercase; text-align: center; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #fff; background-color: #0f172a; padding: 5px 10px; margin-top: 15px; margin-bottom: 5px; }
        .badge-green { background-color: #dcfce7; color: #15803d; padding: 1px 5px; font-size: 8px; font-weight: bold; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; padding: 1px 5px; font-size: 8px; font-weight: bold; }
        .badge-amber { background-color: #fef3c7; color: #b45309; padding: 1px 5px; font-size: 8px; font-weight: bold; }
        .green { color: #059669; }
        .even { background-color: #f8fafc; }
    </style>
</head>
<body>

{{-- En-tete --}}
<div style="text-align:center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px;">
    <h1>{{ $settings->shop_name ?? 'KamerStock' }}</h1>
    @if(!empty($settings->address))<div class="sub">{{ $settings->address }}</div>@endif
    @if(!empty($settings->phone))<div class="sub">{{ $settings->phone }}</div>@endif
    <div class="doc-title">Rapport des Ventes</div>
    <div class="sub">
        Periode : du {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
        au {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        — Genere le {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

{{-- Stats --}}
<table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse; margin-bottom:20px;">
    <tr>
        <td width="25%" style="border:1px solid #e2e8f0; vertical-align:top;">
            <div style="font-size:9px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Chiffre d Affaires</div>
            <div style="font-size:14px; font-weight:bold; color:#0f172a; margin-top:4px;">{{ number_format($totalSales, 0, ',', ' ') }}</div>
            <div style="font-size:9px; color:#94a3b8; margin-top:2px;">{{ $settings->currency ?? 'FCFA' }} — {{ $salesCount }} vente(s)</div>
        </td>
        <td width="25%" style="border:1px solid #e2e8f0; vertical-align:top;">
            <div style="font-size:9px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Marge Commerciale</div>
            <div style="font-size:14px; font-weight:bold; color:#059669; margin-top:4px;">{{ number_format($margin, 0, ',', ' ') }}</div>
            <div style="font-size:9px; color:#94a3b8; margin-top:2px;">{{ $settings->currency ?? 'FCFA' }} — {{ number_format($marginPercentage, 1) }}%</div>
        </td>
        <td width="25%" style="border:1px solid #e2e8f0; vertical-align:top;">
            <div style="font-size:9px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Cout des Ventes</div>
            <div style="font-size:14px; font-weight:bold; color:#0f172a; margin-top:4px;">{{ number_format($cogs, 0, ',', ' ') }}</div>
            <div style="font-size:9px; color:#94a3b8; margin-top:2px;">{{ $settings->currency ?? 'FCFA' }}</div>
        </td>
        <td width="25%" style="border:1px solid #e2e8f0; vertical-align:top;">
            <div style="font-size:9px; color:#94a3b8; font-weight:bold; text-transform:uppercase;">Ventes Annulees</div>
            <div style="font-size:14px; font-weight:bold; color:#0f172a; margin-top:4px;">{{ $cancelledCount }}</div>
            <div style="font-size:9px; color:#94a3b8; margin-top:2px;">Non incluses dans le CA</div>
        </td>
    </tr>
</table>

{{-- Liste des ventes --}}
<div class="section-title">Liste des Ventes</div>

@if($sales->count() > 0)
<table width="100%" cellpadding="5" cellspacing="0" style="border-collapse:collapse; margin-bottom:15px;">
    <thead>
        <tr style="background-color:#0f172a; color:#ffffff;">
            <th style="padding:6px 8px; text-align:left; font-size:9px; font-weight:bold;">#</th>
            <th style="padding:6px 8px; text-align:left; font-size:9px; font-weight:bold;">Date</th>
            <th style="padding:6px 8px; text-align:left; font-size:9px; font-weight:bold;">Client</th>
            <th style="padding:6px 8px; text-align:left; font-size:9px; font-weight:bold;">Caissier</th>
            <th style="padding:6px 8px; text-align:left; font-size:9px; font-weight:bold;">Mode</th>
            <th style="padding:6px 8px; text-align:right; font-size:9px; font-weight:bold;">Remise</th>
            <th style="padding:6px 8px; text-align:right; font-size:9px; font-weight:bold;">Total</th>
            <th style="padding:6px 8px; text-align:center; font-size:9px; font-weight:bold;">Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $index => $sale)
        <tr style="{{ $index % 2 === 0 ? 'background-color:#f8fafc;' : '' }} border-bottom:1px solid #f1f5f9;">
            <td style="padding:5px 8px; font-size:9px; color:#334155;">{{ str_pad($sale->id, 4, '0', STR_PAD_LEFT) }}</td>
            <td style="padding:5px 8px; font-size:9px; color:#334155;">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
            <td style="padding:5px 8px; font-size:9px; color:#334155;">{{ $sale->client?->name ?? 'Passager' }}</td>
            <td style="padding:5px 8px; font-size:9px; color:#334155;">{{ $sale->user->name }}</td>
            <td style="padding:5px 8px; font-size:9px; color:#334155;">{{ $sale->payment_mode_label }}</td>
            <td style="padding:5px 8px; font-size:9px; color:#334155; text-align:right;">{{ $sale->discount > 0 ? number_format($sale->discount, 0, ',', ' ') : '-' }}</td>
            <td style="padding:5px 8px; font-size:9px; color:#334155; text-align:right; font-weight:bold;">{{ number_format($sale->total_amount, 0, ',', ' ') }}</td>
            <td style="padding:5px 8px; font-size:9px; text-align:center;">
                @if($sale->status === 'completee')
                    <span class="badge-green">Complete</span>
                @elseif($sale->status === 'annulee')
                    <span class="badge-red">Annulee</span>
                @else
                    <span class="badge-amber">{{ $sale->status_label }}</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totaux --}}
<table width="50%" cellpadding="4" cellspacing="0" style="border-collapse:collapse; margin-left:50%;">
    <tr>
        <td style="padding:4px 8px; font-size:10px; color:#64748b;">Nombre de ventes :</td>
        <td style="padding:4px 8px; font-size:10px; text-align:right; font-weight:bold;">{{ $salesCount }}</td>
    </tr>
    <tr style="background-color:#f8fafc;">
        <td style="padding:4px 8px; font-size:10px; color:#64748b;">Total remises :</td>
        <td style="padding:4px 8px; font-size:10px; text-align:right;">{{ number_format($totalDiscount, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
    </tr>
    <tr>
        <td style="padding:4px 8px; font-size:10px; color:#64748b;">Cout des ventes :</td>
        <td style="padding:4px 8px; font-size:10px; text-align:right;">{{ number_format($cogs, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
    </tr>
    <tr style="border-top:2px solid #0f172a;">
        <td style="padding:6px 8px; font-size:12px; font-weight:bold; color:#0f172a;">CA TOTAL :</td>
        <td style="padding:6px 8px; font-size:12px; font-weight:bold; text-align:right; color:#0f172a;">{{ number_format($totalSales, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }}</td>
    </tr>
    <tr>
        <td style="padding:4px 8px; font-size:11px; font-weight:bold; color:#059669;">MARGE BRUTE :</td>
        <td style="padding:4px 8px; font-size:11px; font-weight:bold; text-align:right; color:#059669;">{{ number_format($margin, 0, ',', ' ') }} {{ $settings->currency ?? 'FCFA' }} ({{ number_format($marginPercentage, 1) }}%)</td>
    </tr>
</table>

@else
<p style="text-align:center; padding:30px; color:#94a3b8; font-style:italic;">Aucune vente enregistree pour cette periode.</p>
@endif

<div style="text-align:center; margin-top:30px; padding-top:10px; border-top:1px solid #e2e8f0; font-size:9px; color:#94a3b8;">
    {{ $settings->shop_name ?? 'KamerStock' }} — Rapport genere automatiquement — {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>