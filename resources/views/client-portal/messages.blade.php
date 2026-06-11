@extends('layouts.client-portal')

@section('title', 'Messages')

@section('content')

<h1 class="text-lg font-bold text-slate-800 mb-5">Messages</h1>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @forelse($messages as $message)
    <div class="px-4 py-4 border-b border-slate-100">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-bold text-slate-800">{{ $message->subject ?? 'Message' }}</p>
                <p class="text-xs text-slate-400">{{ $message->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                {{ $message->type === 'credit' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600' }}">
                {{ $message->type }}
            </span>
        </div>

        <p class="text-xs text-slate-600 mt-3 leading-relaxed">
            {{ $message->message }}
        </p>
    </div>
    @empty
    <div class="px-4 py-10 text-center text-xs text-slate-400">
        Aucun message.
    </div>
    @endforelse

    @if($messages->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $messages->links() }}
    </div>
    @endif
</div>

@endsection