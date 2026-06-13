@extends('layouts.client-portal')

@section('title', 'Dashboard')

@section('content')

<div class="mb-5">
    <h1 class="text-lg sm:text-xl font-bold text-slate-800">Bonjour {{ $client->name }}</h1>
    <p class="text-xs text-slate-400 mt-1">Bienvenue dans votre espace client KamerStock.</p>
</div>

<div class="grid grid-cols-1 xs:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-5">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Achats</p>
        <p class="text-xl font-bold text-slate-800 mt-1">{{ $salesCount }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Total acheté</p>
        <p class="text-sm font-bold text-slate-800 mt-1 break-words">{{ number_format($totalPurchases, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Crédit restant</p>
        <p class="text-sm font-bold text-red-500 mt-1 break-words">{{ number_format($creditUsed, 0, ',', ' ') }} FCFA</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-slate-400">Messages</p>
        <p class="text-xl font-bold text-amber-600 mt-1">{{ $unreadMessages }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-5">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <h2 class="text-xs font-bold text-slate-700">Derniers achats</h2>
        </div>

        @forelse($recentSales as $sale)
        <div class="px-4 py-3 border-b border-slate-50">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold text-slate-800">Vente #{{ $sale->id }}</p>
                    <p class="text-xs text-slate-400">{{ $sale->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <p class="text-xs font-bold text-slate-700 text-right">
                    {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA
                </p>
            </div>
        </div>
        @empty
        <div class="px-4 py-6 text-center text-xs text-slate-400">Aucun achat.</div>
        @endforelse
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <h2 class="text-xs font-bold text-slate-700">Derniers messages</h2>
        </div>

        @forelse($recentMessages as $message)
        <div class="px-4 py-3 border-b border-slate-50">
            <p class="text-xs font-semibold text-slate-800">{{ $message->subject ?? 'Message' }}</p>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ Str::limit($message->message, 120) }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $message->created_at->format('d/m/Y H:i') }}</p>
        </div>
        @empty
        <div class="px-4 py-6 text-center text-xs text-slate-400">Aucun message.</div>
        @endforelse
    </div>
</div>

@endsection