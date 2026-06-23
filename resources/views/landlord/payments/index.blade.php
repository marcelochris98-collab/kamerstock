@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Paiements & Transactions</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Historique des transactions d'abonnement traitées par la plateforme</p>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Référence</th>
                        <th class="px-6 py-4">Plan / Période</th>
                        <th class="px-6 py-4">Montant</th>
                        <th class="px-6 py-4">Méthode / Date</th>
                        <th class="px-6 py-4 text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('landlord.tenants.show', $pay->tenant) }}" class="font-bold text-slate-900 hover:text-indigo-650">
                                    {{ $pay->tenant?->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $pay->tenant?->slug }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-[10px] text-slate-600 select-all">{{ $pay->reference ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700 leading-none">{{ $pay->subscription?->plan?->name ?? 'Abonnement' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 leading-none">Du {{ $pay->period_start?->format('d/m/Y') }} au {{ $pay->period_end?->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ number_format($pay->amount, 0, ',', ' ') }} {{ $pay->currency }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700 capitalize leading-none">{{ $pay->payment_method ?? 'N/A' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 leading-none">{{ $pay->paid_at ? $pay->paid_at->format('d/m/Y H:i') : 'Non payé' }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    @if($pay->status === 'paid') bg-emerald-50 text-emerald-700
                                    @elseif($pay->status === 'pending') bg-amber-50 text-amber-705
                                    @else bg-red-50 text-red-700 @endif">
                                    {{ $pay->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-450">Aucune transaction de paiement trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
