@extends('layouts.app')

@section('title', 'Messages Clients')
@section('page-title', 'CRM - Messages Clients')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-semibold text-emerald-700">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex h-[600px]">

    <!-- Sidebar: Liste des clients/conversations -->
    <div class="w-1/3 border-r border-slate-100 flex flex-col h-full bg-slate-50/50">
        
        <!-- Search Box -->
        <div class="p-4 border-b border-slate-100 bg-white">
            <form method="GET" action="{{ route('admin.crm_messages.index') }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client..."
                        class="w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <span class="absolute left-2.5 top-2.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                </div>
            </form>
        </div>

        <!-- Clients List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            @forelse($clients as $c)
            @php
                $isActive = $activeClient && $activeClient->id === $c->id;
            @endphp
            <a href="{{ route('admin.crm_messages.index', ['client_id' => $c->id]) }}"
                class="flex items-start gap-3 p-4 transition text-left hover:bg-slate-50/70 {{ $isActive ? 'bg-slate-100/80 border-r-2 border-slate-900' : '' }}">
                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-700 flex-shrink-0">
                    {{ strtoupper(substr($c->name, 0, 1)) }}
                </div>
                
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ $c->name }}</p>
                        @if($c->last_message)
                        <span class="text-[9px] text-slate-400">{{ $c->last_message->created_at->format('d/m H:i') }}</span>
                        @endif
                    </div>
                    
                    <p class="text-[10px] text-slate-500 truncate mt-0.5">
                        {{ $c->last_message ? $c->last_message->message : 'Pas de message' }}
                    </p>
                    
                    <div class="flex items-center gap-1.5 mt-1.5">
                        <span class="text-[8px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 font-medium uppercase">
                            {{ $c->type }}
                        </span>
                        @if($c->unread_count > 0)
                        <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[8px] font-bold">
                            {{ $c->unread_count }}
                        </span>
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div class="p-8 text-center text-xs text-slate-400">
                Aucune conversation trouvée.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Chat Area -->
    <div class="flex-1 flex flex-col h-full bg-slate-50/20">
        @if($activeClient)
        <!-- Chat Header -->
        <div class="px-6 py-3 border-b border-slate-100 bg-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-950 text-white flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(substr($activeClient->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800 leading-none">{{ $activeClient->name }}</h2>
                    <p class="text-[10px] text-slate-400 mt-1">
                        {{ $activeClient->phone ?? 'Aucun numéro' }} · {{ $activeClient->type_label }}
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                @if($activeClient->phone)
                @php
                    $whatsappPhone = preg_replace('/\D+/', '', $activeClient->phone);
                    if (strlen($whatsappPhone) === 9 && str_starts_with($whatsappPhone, '6')) {
                        $whatsappPhone = '237' . $whatsappPhone;
                    }
                @endphp
                <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-100 transition">
                    WhatsApp
                </a>
                @endif
                <a href="{{ route('clients.show', $activeClient) }}"
                    class="px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
                    Fiche Client
                </a>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="adminChatMessages" class="flex-1 p-6 overflow-y-auto space-y-4 bg-slate-50/40">
            @forelse($messages as $msg)
            @php
                $isFromClient = is_null($msg->user_id);
            @endphp
            <div class="flex flex-col {{ $isFromClient ? 'items-start' : 'items-end' }} max-w-[70%] {{ $isFromClient ? 'mr-auto' : 'ml-auto' }}">
                <p class="text-[9px] text-slate-400 mb-0.5 px-1">
                    {{ $isFromClient ? $activeClient->name : ($msg->user->name ?? 'Support') }}
                </p>
                <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs
                    {{ $isFromClient ? 'bg-white text-slate-800 border border-slate-100 rounded-tl-none' : 'bg-slate-900 text-white rounded-tr-none' }}">
                    <p class="leading-relaxed whitespace-pre-line">{{ $msg->message }}</p>
                </div>
                <p class="text-[8px] text-slate-400 mt-1 px-1">
                    {{ $msg->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full text-slate-400 text-xs">
                Aucun message. Démarrez la conversation ci-dessous.
            </div>
            @endforelse
        </div>

        <!-- Chat Input Form -->
        <form id="adminChatForm" onsubmit="sendAdminReply(event)" class="p-4 border-t border-slate-100 bg-white flex gap-2">
            @csrf
            <textarea id="adminReplyText" placeholder="Écrivez votre réponse..." required rows="2"
                class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 resize-none"></textarea>
            <button type="submit"
                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg flex items-center justify-center self-end transition">
                Répondre
            </button>
        </form>
        @else
        <div class="flex-1 flex flex-col items-center justify-center text-slate-400 text-xs p-10">
            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="font-medium">Sélectionnez une conversation pour commencer</p>
            <p class="text-[10px] text-slate-400 mt-1">Vous pourrez échanger avec vos clients en temps réel.</p>
        </div>
        @endif
    </div>
</div>

@if($activeClient)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        scrollAdminChatToBottom();
    });

    function scrollAdminChatToBottom() {
        const area = document.getElementById('adminChatMessages');
        if (area) {
            area.scrollTop = area.scrollHeight;
        }
    }

    function sendAdminReply(event) {
        event.preventDefault();
        const textEl = document.getElementById('adminReplyText');
        const message = textEl.value.trim();
        if (!message) return;

        textEl.value = '';

        // Add temporary bubble
        const container = document.getElementById('adminChatMessages');
        const tempId = 'admin-temp-' + Date.now();
        const tempHtml = `
            <div id="${tempId}" class="flex flex-col items-end max-w-[70%] ml-auto opacity-60">
                <p class="text-[9px] text-slate-400 mb-0.5 px-1">Support</p>
                <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs bg-slate-900 text-white rounded-tr-none">
                    <p class="leading-relaxed whitespace-pre-line">${message}</p>
                </div>
                <p class="text-[8px] text-slate-400 mt-1 px-1">Envoi...</p>
            </div>
        `;
        container.innerHTML += tempHtml;
        scrollAdminChatToBottom();

        fetch("{{ route('admin.crm_messages.reply', $activeClient) }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            const tempBubble = document.getElementById(tempId);
            if (tempBubble) tempBubble.remove();

            if (data.success) {
                // Play send sound
                if (window.userSoundsEnabled) {
                    const audio = new Audio('/sounds/envoi.wav');
                    audio.volume = window.userSoundVolume;
                    audio.play().catch(() => {});
                }

                const msg = data.message;
                const msgHtml = `
                    <div class="flex flex-col items-end max-w-[70%] ml-auto">
                        <p class="text-[9px] text-slate-400 mb-0.5 px-1">Support</p>
                        <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs bg-slate-900 text-white rounded-tr-none">
                            <p class="leading-relaxed whitespace-pre-line">${msg.message}</p>
                        </div>
                        <p class="text-[8px] text-slate-400 mt-1 px-1">${msg.created_at}</p>
                    </div>
                `;
                container.innerHTML += msgHtml;
                scrollAdminChatToBottom();
            }
        })
        .catch(error => {
            console.error('Error replying:', error);
            const tempBubble = document.getElementById(tempId);
            if (tempBubble) {
                tempBubble.querySelector('p.text-[8px]').textContent = 'Erreur d\'envoi';
                tempBubble.querySelector('p.text-[8px]').className = 'text-[8px] text-red-500 mt-1 px-1';
            }
        });
    }

    function refreshAdminChat() {
        fetch("{{ route('admin.crm_messages.history', $activeClient) }}")
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('adminChatMessages');
            if (!container) return;

            const currentBubbleCount = container.querySelectorAll('.flex.flex-col').length;

            if (data.messages.length > 0 && currentBubbleCount !== data.messages.length) {
                container.innerHTML = '';
                data.messages.forEach(msg => {
                    const isFromClient = msg.is_from_client;
                    const name = isFromClient ? data.client.name : 'Support';
                    const msgHtml = `
                        <div class="flex flex-col ${isFromClient ? 'items-start' : 'items-end'} max-w-[70%] ${isFromClient ? 'mr-auto' : 'ml-auto'}">
                            <p class="text-[9px] text-slate-400 mb-0.5 px-1">${name}</p>
                            <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs
                                ${isFromClient ? 'bg-white text-slate-800 border border-slate-100 rounded-tl-none' : 'bg-slate-900 text-white rounded-tr-none'}">
                                <p class="leading-relaxed whitespace-pre-line">${msg.message}</p>
                            </div>
                            <p class="text-[8px] text-slate-400 mt-1 px-1">${msg.created_at}</p>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', msgHtml);
                });
                scrollAdminChatToBottom();
            }
        })
        .catch(err => console.error('Error refreshing admin chat:', err));
    }
</script>
@endif

@endsection
