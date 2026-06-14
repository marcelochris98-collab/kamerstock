@extends('layouts.app')

@section('title', 'Détails du Bon de Commande ' . $order->reference)
@section('page-title', 'Bon de Commande ' . $order->reference)

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if(session('warning'))
<div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-100 rounded-lg text-xs font-medium text-amber-700">
    {{ session('warning') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
    @foreach($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="grid grid-cols-3 gap-5">
    {{-- Infos générales --}}
    <div class="col-span-1 space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-xs font-bold text-slate-700 mb-3 border-b border-slate-50 pb-2">Informations Générales</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400">Référence:</span>
                    <span class="font-semibold text-slate-800">{{ $order->reference }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Date de commande:</span>
                    <span class="font-semibold text-slate-800">{{ $order->order_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Créé par:</span>
                    <span class="font-semibold text-slate-800">{{ $order->user->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Fournisseur:</span>
                    <span class="font-semibold text-slate-800">{{ $order->supplier->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Statut:</span>
                    @if($order->status === 'commande')
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">Commandé</span>
                    @elseif($order->status === 'recu_partiel')
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700">Reçu Partiel</span>
                    @elseif($order->status === 'recu_complet')
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700">Reçu Complet</span>
                    @elseif($order->status === 'annule')
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-600">Annulé</span>
                    @endif
                </div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-bold">
                    <span>Total Estimé:</span>
                    <span>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
            
            @if($order->notes)
            <div class="mt-4 p-3 bg-slate-50 rounded text-[11px] text-slate-600">
                <span class="font-semibold block mb-0.5">Notes:</span>
                {{ $order->notes }}
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-xl shadow-sm p-4 space-y-2">
            <h3 class="text-xs font-bold text-slate-700 mb-3 border-b border-slate-50 pb-2">Actions disponibles</h3>
            
            @if($order->status === 'commande' || $order->status === 'recu_partiel')
            <button onclick="document.getElementById('receptionModal').classList.remove('hidden')"
                class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition text-center">
                Enregistrer une réception
            </button>
            @endif

            @if($order->receptions->count() > 0)
            <form method="POST" action="{{ route('advanced_purchases.orders.convert', $order->id) }}">
                @csrf
                <button type="submit"
                    class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition text-center">
                    Générer la facture d'achat
                </button>
            </form>
            @endif

            @if($order->status === 'commande' || $order->status === 'brouillon')
            <form method="POST" action="{{ route('advanced_purchases.orders.cancel', $order->id) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce bon de commande ?')">
                @csrf
                <button type="submit"
                    class="w-full py-2 border border-red-200 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-50 transition text-center">
                    Annuler le Bon de Commande
                </button>
            </form>
            @endif

            <a href="{{ route('advanced_purchases.orders.index') }}"
                class="block w-full py-2 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition text-center">
                Retour à la liste
            </a>
        </div>
    </div>

    {{-- Articles commandés & Historique des réceptions --}}
    <div class="col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-50">
                <h3 class="text-xs font-bold text-slate-800">Articles commandés</h3>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Commandé</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Reçu</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix Unit.</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition last:border-0">
                        <td class="px-5 py-3">
                            <p class="text-xs font-semibold text-slate-800">{{ $item->product->name }}</p>
                            <p class="text-[10px] text-slate-400">Réf : {{ $item->product->reference }}</p>
                        </td>
                        <td class="px-5 py-3 text-center text-xs text-slate-600">
                            {{ $item->quantity }} {{ $item->product->unit }}
                        </td>
                        <td class="px-5 py-3 text-center text-xs">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $item->quantity_received >= $item->quantity ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                {{ $item->quantity_received }} / {{ $item->quantity }} {{ $item->product->unit }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-slate-600">
                            {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-5 py-3 text-right text-xs font-semibold text-slate-800">
                            {{ number_format($item->subtotal, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Livraisons reçues --}}
        @if($order->receptions->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h3 class="text-xs font-bold text-slate-800 mb-3 border-b border-slate-50 pb-2">Historique des réceptions</h3>
            <div class="space-y-4">
                @foreach($order->receptions as $reception)
                <div class="border border-slate-100 rounded-lg p-3">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <span class="font-bold text-slate-700">{{ $reception->reference }}</span>
                        <span class="text-slate-400">Le {{ $reception->reception_date->format('d/m/Y') }} par {{ $reception->user->name }}</span>
                    </div>
                    @if($reception->notes)
                    <p class="text-[11px] text-slate-500 mb-2 italic">Note : {{ $reception->notes }}</p>
                    @endif
                    <div class="bg-slate-50 rounded p-2">
                        <table class="w-full text-xs">
                            <tbody>
                                @foreach($reception->items as $recItem)
                                <tr>
                                    <td class="py-1 text-slate-600">{{ $recItem->product->name }}</td>
                                    <td class="py-1 text-right font-bold text-slate-700">+{{ $recItem->quantity }} {{ $recItem->product->unit }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- MODAL DE RECEPTION --}}
<div id="receptionModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Enregistrer une Réception de Marchandises</h3>
            <button onclick="document.getElementById('receptionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">×</button>
        </div>
        <form method="POST" action="{{ route('advanced_purchases.orders.receive', $order->id) }}" class="p-5 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Date de Réception <span class="text-red-400">*</span></label>
                <input type="date" name="reception_date" value="{{ now()->toDateString() }}" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="border-t border-b border-slate-50 py-3 space-y-2">
                <p class="text-[11px] font-semibold text-slate-400 mb-1">Saisir les quantités reçues :</p>
                @foreach($order->items as $item)
                @php
                    $maxToReceive = max(0, $item->quantity - $item->quantity_received);
                @endphp
                @if($maxToReceive > 0)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-700 truncate w-1/2">{{ $item->product->name }}</span>
                    <span class="text-slate-400">Reste : {{ $maxToReceive }} {{ $item->product->unit }}</span>
                    <input type="number" name="quantities[{{ $item->id }}]" value="{{ $maxToReceive }}" min="0" max="{{ $maxToReceive }}"
                        class="w-20 px-2 py-1 border border-slate-200 rounded text-center text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                @endif
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes de livraison (N° de bon, etc.)</label>
                <textarea name="notes" rows="2" placeholder="N° de bon du transporteur, remarques sur l'état..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 resize-none"></textarea>
            </div>

            <div class="flex gap-2 justify-end pt-2">
                <button type="button" onclick="document.getElementById('receptionModal').classList.add('hidden')"
                    class="px-4 py-1.5 border border-slate-200 text-slate-600 text-xs font-semibold rounded hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded hover:bg-slate-700 transition">
                    Valider la Réception
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
