@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Accès Support Technique</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Autorisations de connexion temporaire aux boutiques accordées par les utilisateurs</p>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Raison de la demande</th>
                        <th class="px-6 py-4">Période accordée</th>
                        <th class="px-6 py-4">Révocation</th>
                        <th class="px-6 py-4 text-right">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supportAccesses as $access)
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('landlord.tenants.show', $access->tenant) }}" class="font-bold text-slate-900 hover:text-indigo-650">
                                    {{ $access->tenant?->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $access->tenant?->slug }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 font-medium max-w-xs truncate" title="{{ $access->reason }}">
                                {{ $access->reason }}
                            </td>
                            <td class="px-6 py-4">
                                Du {{ $access->starts_at?->format('d/m/Y H:i') }}<br>
                                Au {{ $access->ends_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                @if($access->revoked_at)
                                    Révoké le {{ $access->revoked_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-slate-400">Non révoqué</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                    @if($access->status === 'approved') bg-emerald-50 text-emerald-700
                                    @elseif($access->status === 'pending') bg-amber-50 text-amber-705
                                    @else bg-red-50 text-red-700 @endif">
                                    {{ $access->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-450">Aucune demande d'accès support enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($supportAccesses->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $supportAccesses->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
