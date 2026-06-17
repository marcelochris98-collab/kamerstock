@extends('layouts.app')

@section('title', 'Dashboard achats')
@section('page-title', 'Dashboard achats & fournisseurs')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Dashboard achats & fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">Vue globale des achats, paiements et dettes fournisseurs.</p>
    </div>

    <a href="{{ route('purchases.create') }}"
        class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700">
        Nouvel achat
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <div class="rounded-xl shadow-sm p-5 bg-gradient-to-br from-slate-900 via-slate-700 to-slate-500 text-white">
        <p class="text-xs text-slate-200">Total achats</p>
        <p class="text-xl font-bold mt-2">
            {{ number_format($totalPurchases, 0, ',', ' ') }} FCFA
        </p>
    </div>

    <div class="rounded-xl shadow-sm p-5 bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-400 text-white">
        <p class="text-xs text-emerald-50">Total payé</p>
        <p class="text-xl font-bold mt-2">
            {{ number_format($totalPaid, 0, ',', ' ') }} FCFA
        </p>
    </div>

    <div class="rounded-xl shadow-sm p-5 bg-gradient-to-br from-blue-950 via-slate-900 to-blue-800 text-white">
        <p class="text-xs text-blue-100">Dette fournisseurs</p>
        <p class="text-xl font-bold mt-2">
            {{ number_format($totalDue, 0, ',', ' ') }} FCFA
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-xs font-semibold text-slate-700">Derniers achats</h2>
        </div>

        <table class="w-full">
            <tbody>
                @forelse($recentPurchases as $purchase)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="px-5 py-3">
                            <p class="text-xs font-semibold text-slate-800">{{ $purchase->reference }}</p>
                            <p class="text-xs text-slate-400">{{ $purchase->supplier?->name ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3 text-right text-xs font-semibold text-slate-700">
                            {{ number_format($purchase->total_amount, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-8 text-center text-xs text-slate-400">Aucun achat récent.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-xs font-semibold text-slate-700">Top fournisseurs</h2>
        </div>

        <table class="w-full">
            <tbody>
                @forelse($topSuppliers as $supplier)
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="px-5 py-3">
                            <p class="text-xs font-semibold text-slate-800">{{ $supplier->name }}</p>
                            <p class="text-xs text-slate-400">{{ $supplier->phone ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3 text-right text-xs font-semibold text-slate-700">
                            {{ number_format($supplier->purchases_sum_total_amount ?? 0, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-5 py-8 text-center text-xs text-slate-400">Aucun fournisseur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

