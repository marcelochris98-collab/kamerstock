@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Centre de notifications</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $notifications->total() }} notification(s)</p>
    </div>

    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button type="submit"
            class="px-3 py-2 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
            Tout marquer comme lu
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="divide-y divide-slate-50">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.read', $notification) }}"
                class="block px-5 py-4 hover:bg-slate-50 transition {{ !$notification->is_read ? 'bg-amber-50/40' : '' }}">

                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center
                        {{ !$notification->is_read ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-slate-800">
                                {{ $notification->title }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $notification->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        @if($notification->message)
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $notification->message }}
                            </p>
                        @endif

                        <p class="text-xs text-slate-300 mt-1">
                            Type : {{ $notification->type }}
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-5 py-12 text-center">
                <p class="text-sm font-medium text-slate-400">Aucune notification</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@endsection
