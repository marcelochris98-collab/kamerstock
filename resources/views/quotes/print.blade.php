<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quote->type_label }} #{{ $quote->reference }}</title>
    <!-- Tailwind CSS CDN for styling printable page -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background-color: white;
                color: black;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-6 sm:p-10 font-sans text-slate-800 text-xs">

    <!-- Top Action bar (hidden when printing) -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print bg-white p-4 rounded-xl shadow-sm border border-slate-100">
        <span class="font-medium text-slate-700">Aperçu du document avant impression</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-semibold transition">
                Lancer l'impression / Enregistrer en PDF
            </button>
            <button onclick="window.close()" class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                Fermer l'onglet
            </button>
        </div>
    </div>

    <!-- Main Printable Invoice sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-md rounded-xl border border-slate-200" id="printSheet">
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-slate-100 pb-6 mb-6">
            <div>
                <h1 class="text-xl font-black text-slate-900 uppercase">{{ $settings->shop_name ?? 'KamerStock' }}</h1>
                <p class="text-slate-400 mt-1">Quincaillerie générale & Outillage</p>
                <div class="text-[10px] text-slate-400 mt-2 space-y-0.5">
                    @if($settings->address) <p>Adresse : {{ $settings->address }}</p> @endif
                    @if($settings->phone) <p>Tél : {{ $settings->phone }}</p> @endif
                    @if($settings->email) <p>Email : {{ $settings->email }}</p> @endif
                </div>
            </div>
            
            <div class="text-right">
                <h2 class="text-lg font-black text-slate-800 uppercase tracking-wider">{{ $quote->type_label }}</h2>
                <p class="text-slate-500 font-semibold mt-1">#{{ $quote->reference }}</p>
                <div class="text-[10px] text-slate-400 mt-2 space-y-0.5">
                    <p>Date d'émission : {{ $quote->created_at->format('d/m/Y') }}</p>
                    @if($quote->valid_until)
                    <p class="text-amber-600 font-medium">Valide jusqu'au : {{ $quote->valid_until->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Addresses block -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-slate-50 p-4 rounded-lg">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-2">Émetteur</p>
                <p class="font-bold text-slate-800">{{ $settings->shop_name ?? 'KamerStock' }}</p>
                <p class="text-slate-500 mt-1">{{ $settings->address ?? 'Cameroun' }}</p>
            </div>

            <div class="bg-slate-50 p-4 rounded-lg">
                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-2">Destinataire</p>
                <p class="font-bold text-slate-800">{{ $quote->client->name ?? 'Passager' }}</p>
                <p class="text-slate-500 mt-1">
                    {{ $quote->client->phone ?? '—' }} <br>
                    {{ $quote->client->address ?? '—' }}
                </p>
            </div>
        </div>

        <!-- Line items table -->
        <table class="w-full mb-8 text-xs">
            <thead>
                <tr class="border-b border-slate-200 text-slate-400 uppercase text-[10px] tracking-wider">
                    <th class="pb-2 text-left font-semibold">Description</th>
                    <th class="pb-2 text-center font-semibold w-16">Qté</th>
                    <th class="pb-2 text-right font-semibold w-32">Prix unitaire</th>
                    <th class="pb-2 text-right font-semibold w-32">Total HT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @foreach($quote->details as $detail)
                <tr>
                    <td class="py-3 font-semibold text-slate-900">{{ $detail->product->name }}</td>
                    <td class="py-3 text-center">{{ $detail->quantity }}</td>
                    <td class="py-3 text-right">{{ number_format($detail->unit_price, 0, ',', ' ') }} F</td>
                    <td class="py-3 text-right font-bold text-slate-800">{{ number_format($detail->subtotal, 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux -->
        <div class="flex justify-end mb-10">
            <div class="w-64 space-y-2 text-xs text-slate-600">
                <div class="flex justify-between">
                    <p>Sous-total HT</p>
                    <p class="font-semibold text-slate-800">{{ number_format($quote->subtotal, 0, ',', ' ') }} F</p>
                </div>
                <div class="flex justify-between">
                    <p>TVA ({{ $settings->tax_rate ?? 17.5 }}%)</p>
                    <p class="font-semibold text-slate-800">{{ number_format($quote->tax_amount, 0, ',', ' ') }} F</p>
                </div>
                <div class="flex justify-between text-slate-900 font-bold text-sm pt-2 border-t border-slate-200">
                    <p>Total TTC</p>
                    <p>{{ number_format($quote->total_amount, 0, ',', ' ') }} F</p>
                </div>
            </div>
        </div>

        <!-- Footer notes -->
        @if($quote->notes)
        <div class="border-t border-slate-100 pt-4 text-[10px] text-slate-400 italic">
            <p><strong>Note :</strong> {{ $quote->notes }}</p>
        </div>
        @endif

        <!-- Signature stamp area -->
        <div class="grid grid-cols-2 gap-8 mt-16 pt-8 border-t border-slate-100 text-center text-slate-400">
            <div>
                <p class="font-bold text-slate-600 mb-10">Le Client</p>
                <p class="text-[9px] italic">Signature précédée de la mention "Lu et approuvé"</p>
            </div>
            <div>
                <p class="font-bold text-slate-600 mb-10">Pour KamerStock (Bon pour accord)</p>
                <p class="text-[9px] italic">Cachet et Signature</p>
            </div>
        </div>
    </div>

</body>
</html>
