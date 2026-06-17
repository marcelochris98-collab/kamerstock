@extends('layouts.app')

@section('title', 'Journal d\'audit')
@section('page-title', 'Journal d\'audit')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Journal d'audit</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $logs->total() }} action(s) enregistree(s)</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-slate-500 mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Action, utilisateur, IP..."
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Utilisateur</label>
            <select name="user_id"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <option value="">Tous</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Du</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Au</label>
            <div class="flex gap-2">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                <button type="submit"
                    class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                    Filtrer
                </button>
            </div>
        </div>
    </form>

    @if(request()->hasAny(['search', 'user_id', 'date_from', 'date_to']))
    <div class="mt-3">
        <a href="{{ route('admin.audit-logs.index') }}"
            class="inline-flex px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-lg transition">
            Reinitialiser
        </a>
    </div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($logs->isEmpty())
        <div class="px-5 py-12 text-center">
            <p class="text-sm font-medium text-slate-400">Aucune action enregistree.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date / heure</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Utilisateur</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Action</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Description</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                        <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">
                            {{ $log->created_at?->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-xs font-medium text-slate-800">{{ $log->user?->name ?? 'Systeme' }}</p>
                            @if($log->user?->email)
                                <p class="text-xs text-slate-400">{{ $log->user->email }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-medium">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500 min-w-64">
                            {{ $log->description ?: '-' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-400 whitespace-nowrap">
                            {{ $log->ip_address ?: '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-slate-50">
            {{ $logs->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
