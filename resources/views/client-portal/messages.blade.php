@extends('layouts.client-portal')

@section('title', 'Messagerie Support')

@section('content')

<div class="mb-5">
    <h1 class="text-lg font-bold text-slate-800">Messagerie Support</h1>
    <p class="text-xs text-slate-400 mt-1">Échangez en temps réel avec l'équipe de KamerStock.</p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex h-[600px]">

    <!-- Volet Gauche : Infos Support & Boutique -->
    <div class="hidden md:flex w-1/3 border-r border-slate-100 flex-col h-full bg-slate-50/50 p-6 space-y-6">
        <div>
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg mb-3">
                {{ strtoupper(substr($client->name, 0, 1)) }}
            </div>
            <h3 class="text-sm font-bold text-slate-800">{{ $client->name }}</h3>
            <p class="text-xs text-slate-450 mt-0.5">{{ $client->phone ?? 'Pas de téléphone' }}</p>
            <p class="text-[10px] bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full inline-block mt-2 font-semibold uppercase">
                Espace Client
            </p>
        </div>

        <div class="border-t border-slate-200/60 pt-5 space-y-4">
            <div>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Boutique</p>
                <p class="text-xs font-bold text-slate-700 mt-1">KamerStock</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Votre quincaillerie de confiance</p>
            </div>

            @php
                $settings = \App\Models\Setting::first();
            @endphp
            @if($settings)
            <div class="space-y-2.5">
                @if($settings->address)
                <div class="flex items-start gap-2 text-[11px] text-slate-600">
                    <svg class="w-3.5 h-3.5 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $settings->address }}</span>
                </div>
                @endif
                @if($settings->phone)
                <div class="flex items-center gap-2 text-[11px] text-slate-600">
                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>{{ $settings->phone }}</span>
                </div>
                @php
                    $whatsappPhone = preg_replace('/\D+/', '', $settings->phone);
                    if (strlen($whatsappPhone) === 9 && str_starts_with($whatsappPhone, '6')) {
                        $whatsappPhone = '237' . $whatsappPhone;
                    }
                @endphp
                <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank"
                    class="mt-2 flex items-center justify-center gap-2 w-full py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg transition border border-emerald-150">
                    <svg class="w-4 h-4 fill-emerald-600" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.455L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.725 1.451 5.437 0 9.862-4.43 9.865-9.87.001-2.636-1.02-5.115-2.873-6.97C16.512 1.86 14.04 .843 11.4 0.843 5.965.843 1.54 5.272 1.537 10.713c-.001 1.61.424 3.18 1.231 4.588l-1.02 3.725 3.82-.999z"/>
                    </svg>
                    <span>Nous écrire sur WhatsApp</span>
                </a>
                @endif
            </div>
            @endif
        </div>

        <div class="border-t border-slate-200/60 pt-5 text-center mt-auto">
            <p class="text-[10px] text-slate-400">Échanges chiffrés et sécurisés</p>
        </div>
    </div>

    <!-- Right Chat Area -->
    <div class="flex-1 flex flex-col h-full bg-slate-50/20">
        <!-- Chat Header -->
        <div class="px-6 py-3 border-b border-slate-100 bg-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
                    K
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-800 leading-none">Support KamerStock</h2>
                    <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block animate-pulse"></span>
                        Boutique en ligne
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="clientChatMessages" class="flex-1 p-6 overflow-y-auto space-y-4 bg-slate-50/40">
            @forelse($messages as $msg)
            @php
                $isFromClient = is_null($msg->user_id);
            @endphp
            <div class="flex flex-col {{ $isFromClient ? 'items-end' : 'items-start' }} max-w-[80%] {{ $isFromClient ? 'ml-auto' : 'mr-auto' }}">
                <p class="text-[9px] text-slate-400 mb-0.5 px-1">
                    {{ $isFromClient ? 'Vous' : ($msg->user->name ?? 'Support') }} 
                    @if(!$isFromClient && $msg->type)
                    <span class="text-[8px] bg-slate-200/70 text-slate-600 px-1 py-0.2 rounded font-semibold uppercase ml-1">
                        {{ $msg->type }}
                    </span>
                    @endif
                </p>
                <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs
                    {{ $isFromClient ? 'bg-slate-900 text-white rounded-tr-none' : 'bg-white text-slate-850 border border-slate-100 rounded-tl-none' }}">
                    <p class="leading-relaxed whitespace-pre-line">{{ $msg->message }}</p>
                </div>
                <div class="flex items-center gap-1 mt-1 px-1">
                    <p class="text-[8px] text-slate-400">
                        {{ $msg->created_at->format('d/m/Y H:i') }}
                    </p>
                    @if($isFromClient)
                        @if($msg->read_at)
                            <!-- Double check vert/orange pour lu -->
                            <span class="text-emerald-500 font-bold" title="Lu le {{ $msg->read_at->format('d/m H:i') }}">✓✓</span>
                        @else
                            <!-- Double check gris pour envoyé -->
                            <span class="text-slate-300 font-bold" title="Délivré">✓✓</span>
                        @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full text-slate-400 text-xs py-10">
                <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="font-semibold text-slate-500">Aucun message</p>
                <p class="text-[10px] text-slate-450 mt-1">Envoyez votre premier message ci-dessous pour lancer la discussion.</p>
            </div>
            @endforelse
        </div>

        <!-- Chat Input Form -->
        <form id="clientChatForm" onsubmit="sendClientMessage(event)" class="p-4 border-t border-slate-100 bg-white flex gap-2">
            @csrf
            <textarea id="clientMessageText" placeholder="Écrivez votre message..." required rows="2"
                class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 resize-none"></textarea>
            <button type="submit"
                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg flex items-center justify-center self-end transition">
                Envoyer
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        scrollClientChatToBottom();
        
        // Polling des messages toutes les 15 secondes
        setInterval(refreshClientChat, 15000);
    });

    function scrollClientChatToBottom() {
        const area = document.getElementById('clientChatMessages');
        if (area) {
            area.scrollTop = area.scrollHeight;
        }
    }

    function sendClientMessage(event) {
        event.preventDefault();
        const textEl = document.getElementById('clientMessageText');
        const message = textEl.value.trim();
        if (!message) return;

        textEl.value = '';

        // Ajouter une bulle temporaire
        const container = document.getElementById('clientChatMessages');
        
        // Retirer le conteneur vide s'il existe
        const emptyDiv = container.querySelector('.text-center');
        if (emptyDiv) emptyDiv.remove();
        
        const tempId = 'client-temp-' + Date.now();
        const tempHtml = `
            <div id="${tempId}" class="flex flex-col items-end max-w-[80%] ml-auto opacity-60">
                <p class="text-[9px] text-slate-400 mb-0.5 px-1">Vous</p>
                <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs bg-slate-900 text-white rounded-tr-none">
                    <p class="leading-relaxed whitespace-pre-line">${message}</p>
                </div>
                <div class="flex items-center gap-1 mt-1 px-1">
                    <p class="text-[8px] text-slate-450">Envoi...</p>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', tempHtml);
        scrollClientChatToBottom();

        fetch("{{ route('client.portal.messages.send_ajax') }}", {
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
                if (window.clientSoundsEnabled) {
                    const audio = new Audio('/sounds/envoi.wav');
                    audio.volume = 0.25;
                    audio.play().catch(function () {});
                }

                const msg = data.message;
                const msgHtml = `
                    <div class="flex flex-col items-end max-w-[80%] ml-auto">
                        <p class="text-[9px] text-slate-400 mb-0.5 px-1">Vous</p>
                        <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs bg-slate-900 text-white rounded-tr-none">
                            <p class="leading-relaxed whitespace-pre-line">${msg.message}</p>
                        </div>
                        <div class="flex items-center gap-1 mt-1 px-1">
                            <p class="text-[8px] text-slate-400">${msg.created_at}</p>
                            <span class="text-slate-300 font-bold" title="Délivré">✓✓</span>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', msgHtml);
                scrollClientChatToBottom();
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            const tempBubble = document.getElementById(tempId);
            if (tempBubble) {
                tempBubble.querySelector('p.text-[8px]').textContent = 'Erreur d\'envoi';
                tempBubble.querySelector('p.text-[8px]').className = 'text-[8px] text-red-500';
            }
        });
    }

    function refreshClientChat() {
        fetch("{{ route('client.portal.messages.json') }}")
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('clientChatMessages');
            if (!container) return;

            // Compter les bulles actuelles non-temporaires
            const currentBubbleCount = container.querySelectorAll('.flex.flex-col').length;
            
            // Si le nombre de messages a changé, reconstruire le chat
            if (data.messages.length > 0 && currentBubbleCount !== data.messages.length) {
                // Play receive sound if the new last message is from support
                const lastMsg = data.messages[data.messages.length - 1];
                if (lastMsg && !lastMsg.is_from_client) {
                    if (window.clientSoundsEnabled) {
                        const audio = new Audio('/sounds/reception.wav');
                        audio.volume = 0.25;
                        audio.play().catch(function () {});
                    }
                }

                container.innerHTML = '';
                data.messages.forEach(msg => {
                    const isClient = msg.is_from_client;
                    const checkmarks = isClient ? (msg.read_at ? '<span class="text-emerald-500 font-bold" title="Lu">✓✓</span>' : '<span class="text-slate-300 font-bold" title="Envoyé">✓✓</span>') : '';
                    
                    const roleLabel = !isClient && msg.type ? `<span class="text-[8px] bg-slate-200/70 text-slate-600 px-1 py-0.2 rounded font-semibold uppercase ml-1">${msg.type}</span>` : '';
                    
                    const msgHtml = `
                        <div class="flex flex-col ${isClient ? 'items-end' : 'items-start'} max-w-[80%] ${isClient ? 'ml-auto' : 'mr-auto'}">
                            <p class="text-[9px] text-slate-400 mb-0.5 px-1">
                                ${isClient ? 'Vous' : 'Support'} ${roleLabel}
                            </p>
                            <div class="px-3 py-2.5 rounded-lg shadow-sm text-xs
                                ${isClient ? 'bg-slate-900 text-white rounded-tr-none' : 'bg-white text-slate-850 border border-slate-100 rounded-tl-none'}">
                                <p class="leading-relaxed whitespace-pre-line">${msg.message}</p>
                            </div>
                            <div class="flex items-center gap-1 mt-1 px-1">
                                <p class="text-[8px] text-slate-400">${msg.created_at}</p>
                                ${checkmarks}
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', msgHtml);
                });
                scrollClientChatToBottom();
            }
        })
        .catch(err => console.error('Error refreshing chat:', err));
    }
</script>

@endsection