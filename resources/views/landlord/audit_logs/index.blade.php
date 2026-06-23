@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Journal d'Audit Plateforme</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Traces d'activité de la console super administrateur</p>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Utilisateur Landlord</th>
                        <th class="px-6 py-4">Boutique ciblée</th>
                        <th class="px-6 py-4">Action</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4">IP / Agent utilisateur</th>
                        <th class="px-6 py-4 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-900 leading-none">{{ $log->landlordUser?->name ?? 'Système' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 leading-none">{{ $log->landlordUser?->email ?? 'Platform' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @if($log->tenant)
                                    <a href="{{ route('landlord.tenants.show', $log->tenant) }}" class="font-semibold text-indigo-650">
                                        {{ $log->tenant->name }}
                                    </a>
                                @else
                                    <span class="text-slate-400">Toutes / Aucune</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 uppercase tracking-wide">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-650">{{ $log->description }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700 leading-none">{{ $log->ip_address }}</p>
                                <p class="text-[9px] text-slate-400 mt-1 leading-tight max-w-xs truncate" title="{{ $log->user_agent }}">{{ $log->user_agent }}</p>
                            </td>
                            <td class="px-6 py-4 text-right text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-450">Aucun journal d'audit enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
