@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Abonnements Boutiques</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Consultez l'historique et l'état des abonnements de toutes les boutiques</p>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Plan souscrit</th>
                        <th class="px-6 py-4">Période de facturation</th>
                        <th class="px-6 py-4">Montant / Devise</th>
                        <th class="px-6 py-4 text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('landlord.tenants.show', $sub->tenant) }}" class="font-bold text-slate-900 hover:text-indigo-650">
                                    {{ $sub->tenant?->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $sub->tenant?->slug }}</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700">{{ $sub->plan?->name ?? 'Plan personnalisé' }}</td>
                            <td class="px-6 py-4">
                                Du {{ $sub->starts_at?->format('d/m/Y') }} au {{ $sub->ends_at?->format('d/m/Y') }}
                                <span class="block text-[10px] text-slate-400 mt-0.5 capitalize">Cycle: {{ $sub->billing_cycle ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ number_format($sub->amount, 0, ',', ' ') }} {{ $sub->currency }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    @if($sub->status === 'active') bg-emerald-50 text-emerald-700
                                    @elseif($sub->status === 'trial') bg-indigo-50 text-indigo-750
                                    @else bg-red-50 text-red-700 @endif">
                                    {{ $sub->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-450">Aucun abonnement trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
