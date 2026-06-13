@extends('layouts.client-portal')

@section('title', 'Messages')

@section('content')

<div class="mb-5">
    <h1 class="text-lg font-bold text-slate-800">Messages</h1>
    <p class="text-xs text-slate-400 mt-1">Échangez avec l'entreprise depuis votre espace client.</p>
</div>

<form method="POST" action="{{ route('client.portal.messages.send') }}"
    class="bg-white rounded-xl shadow-sm p-4 mb-5">
    @csrf

    <div class="mb-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Sujet</label>
        <input type="text" name="subject" placeholder="Ex : Question sur mon crédit"
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
    </div>

    <div class="mb-3">
        <label class="block text-xs font-semibold text-slate-600 mb-1">Message</label>
        <textarea name="message" rows="4" required
            placeholder="Écrivez votre message..."
            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs"></textarea>
    </div>

    <button type="submit"
        class="w-full sm:w-auto px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold">
        Envoyer le message
    </button>
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @forelse($messages as $message)
    @php
        $fromClient = is_null($message->user_id);
    @endphp

    <div class="px-4 py-4 border-b border-slate-100 {{ $fromClient ? 'bg-amber-50/40' : 'bg-white' }}">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold {{ $fromClient ? 'text-amber-700' : 'text-slate-800' }}">
                    {{ $message->subject ?? 'Message' }}
                </p>
                <p class="text-xs text-slate-400">
                    {{ $fromClient ? 'Vous' : 'Entreprise' }} · {{ $message->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                {{ $fromClient ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }}">
                {{ $fromClient ? 'Client' : $message->type }}
            </span>
        </div>

        <p class="text-xs text-slate-600 mt-3 leading-relaxed whitespace-pre-line">
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