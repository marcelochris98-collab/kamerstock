<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamerStock Client | @yield('title', 'Portail')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen">

    <div class="bg-slate-900 text-white px-4 py-4">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div>
                <p class="text-sm font-bold">KamerStock</p>
                <p class="text-xs text-slate-400">Portail client</p>
            </div>

            @if(session('portal_client_id'))
            <form method="POST" action="{{ route('client.portal.logout') }}">
                @csrf
                <button class="text-xs px-3 py-2 bg-white/10 rounded-lg hover:bg-white/20">
                    Déconnexion
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('portal_client_id'))
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 py-3 flex gap-2 overflow-x-auto">
            <a href="{{ route('client.portal.dashboard') }}"
                class="px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap {{ request()->routeIs('client.portal.dashboard') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                Accueil
            </a>

            <a href="{{ route('client.portal.sales') }}"
                class="px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap {{ request()->routeIs('client.portal.sales') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                Mes achats
            </a>

            <a href="{{ route('client.portal.credits') }}"
                class="px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap {{ request()->routeIs('client.portal.credits') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                Mes crédits
            </a>

            <a href="{{ route('client.portal.messages') }}"
                class="px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap {{ request()->routeIs('client.portal.messages') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                Messages
            </a>
        </div>
    </div>
    @endif

    <main class="max-w-5xl mx-auto px-4 py-6">
        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </main>

    @if(session('portal_client_id'))
    @php
        $unreadMessagesCount = \App\Models\ClientMessage::where('client_id', session('portal_client_id'))
            ->whereNotNull('user_id')
            ->whereNull('read_at')
            ->count();
    @endphp
    <!-- Floating Chat Button -->
    <button id="chatButton" onclick="toggleChat()" class="fixed bottom-6 right-6 w-12 h-12 bg-slate-900 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-slate-800 transition z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span id="chatBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $unreadMessagesCount > 0 ? '' : 'hidden' }}">
            {{ $unreadMessagesCount }}
        </span>
    </button>

    <!-- Chat Window -->
    <div id="chatWindow" class="fixed bottom-20 right-6 w-80 sm:w-96 h-[450px] bg-white rounded-xl shadow-2xl border border-slate-200 flex flex-col hidden z-50 overflow-hidden transition-all duration-300 transform translate-y-4 opacity-0 scale-95">
        <!-- Header -->
        <div class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></div>
                <div>
                    <p class="text-xs font-bold">Support KamerStock</p>
                    <p class="text-[10px] text-slate-400">En ligne pour vous aider</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Messages Area -->
        <div id="chatMessages" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50 flex flex-col">
            <p class="text-center text-[10px] text-slate-400 my-4">Chargement de la conversation...</p>
        </div>

        <!-- Input Area -->
        <form id="chatForm" onsubmit="sendChatMessage(event)" class="p-3 border-t border-slate-100 flex gap-2 bg-white">
            <input type="text" id="chatInput" placeholder="Écrivez votre message..." required class="flex-1 px-3 py-1.5 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900">
            <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition">
                Envoyer
            </button>
        </form>
    </div>

    <script>
        @if(session('portal_client_id'))
        @php
            $clientModel = \App\Models\Client::find(session('portal_client_id'));
        @endphp
        window.clientSoundsEnabled = {{ ($clientModel && $clientModel->sounds_enabled) ? 'true' : 'false' }};
        @else
        window.clientSoundsEnabled = false;
        @endif

        let chatOpen = false;
        let clientLastCheckedTime = '{{ now()->toIso8601String() }}';

        function toggleChat() {
            const chatWin = document.getElementById('chatWindow');
            const chatBadge = document.getElementById('chatBadge');
            
            if (chatOpen) {
                chatWin.classList.add('scale-95', 'translate-y-4', 'opacity-0');
                setTimeout(() => chatWin.classList.add('hidden'), 300);
                chatOpen = false;
            } else {
                chatWin.classList.remove('hidden');
                setTimeout(() => {
                    chatWin.classList.remove('scale-95', 'translate-y-4', 'opacity-0');
                }, 50);
                chatOpen = true;
                chatBadge.classList.add('hidden');
                loadChatMessages();
            }
        }

        function loadChatMessages() {
            const container = document.getElementById('chatMessages');
            fetch("{{ route('client.portal.messages.json') }}")
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = '';
                    if (data.messages.length === 0) {
                        container.innerHTML = '<p class="text-center text-[11px] text-slate-400 my-10 font-medium">Aucun message. Posez votre question ci-dessous !</p>';
                        return;
                    }
                    data.messages.forEach(msg => {
                        const isClient = msg.is_from_client;
                        const msgHtml = `
                            <div class="flex flex-col ${isClient ? 'items-end' : 'items-start'} max-w-[85%] ${isClient ? 'self-end' : 'self-start'}">
                                <p class="text-[9px] text-slate-400 mb-0.5 px-1">${isClient ? 'Vous' : 'Support'}</p>
                                <div class="px-3 py-2 rounded-lg shadow-sm text-xs ${isClient ? 'bg-slate-900 text-white rounded-tr-none' : 'bg-white text-slate-800 border border-slate-150 rounded-tl-none'}">
                                    <p class="leading-relaxed whitespace-pre-line">${msg.message}</p>
                                </div>
                                <p class="text-[8px] text-slate-400 mt-0.5 px-1">${msg.created_at}</p>
                            </div>
                        `;
                        container.innerHTML += msgHtml;
                    });
                    scrollChatToBottom();
                })
                .catch(error => {
                    console.error('Error loading chat:', error);
                    container.innerHTML = '<p class="text-center text-xs text-red-500 my-10">Erreur de chargement. Veuillez réessayer.</p>';
                });
        }

        function sendChatMessage(event) {
            event.preventDefault();
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            if (!message) return;

            input.value = '';
            
            const container = document.getElementById('chatMessages');
            const tempId = 'temp-' + Date.now();
            const tempHtml = `
                <div id="${tempId}" class="flex flex-col items-end max-w-[85%] self-end opacity-60">
                    <p class="text-[9px] text-slate-400 mb-0.5 px-1">Vous</p>
                    <div class="px-3 py-2 rounded-lg shadow-sm text-xs bg-slate-900 text-white rounded-tr-none">
                        <p class="leading-relaxed whitespace-pre-line">${message}</p>
                    </div>
                    <p class="text-[8px] text-slate-400 mt-0.5 px-1">Envoi...</p>
                </div>
            `;
            container.innerHTML += tempHtml;
            scrollChatToBottom();

            fetch("{{ route('client.portal.messages.send_ajax') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                const tempBubble = document.getElementById(tempId);
                if (tempBubble) tempBubble.remove();

                if (data.success) {
                    if (window.clientSoundsEnabled) {
                        const audio = new Audio('/sounds/envoi.wav');
                        audio.volume = 0.25;
                        audio.play().catch(function () {});
                    }

                    const msg = data.message;
                    const msgHtml = `
                        <div class="flex flex-col items-end max-w-[85%] self-end">
                            <p class="text-[9px] text-slate-400 mb-0.5 px-1">Vous</p>
                            <div class="px-3 py-2 rounded-lg shadow-sm text-xs bg-slate-900 text-white rounded-tr-none">
                                <p class="leading-relaxed whitespace-pre-line">${msg.message}</p>
                            </div>
                            <p class="text-[8px] text-slate-400 mt-0.5 px-1">${msg.created_at}</p>
                        </div>
                    `;
                    container.innerHTML += msgHtml;
                    scrollChatToBottom();
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                const tempBubble = document.getElementById(tempId);
                if (tempBubble) {
                    tempBubble.querySelector('p.text-[8px]').textContent = 'Erreur d\'envoi';
                    tempBubble.querySelector('p.text-[8px]').className = 'text-[8px] text-red-500 mt-0.5 px-1';
                }
            });
        }

        function scrollChatToBottom() {
            const container = document.getElementById('chatMessages');
            container.scrollTop = container.scrollHeight;
        }

        function showClientToast(msg) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-20 right-6 z-[9999] bg-white shadow-lg rounded-xl px-4 py-3 max-w-sm border border-emerald-100 transition-all duration-300';
            toast.innerHTML = `
                <div class="flex items-center justify-between gap-4">
                    <p class="text-xs font-bold text-emerald-700">${msg.title}</p>
                    <button class="text-slate-400 hover:text-slate-600" onclick="this.parentElement.parentElement.remove()">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1">${msg.message}</p>
            `;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 6000);
        }

        function pollClientMessages() {
            fetch(`/client/notifications/poll?last_checked=${encodeURIComponent(clientLastCheckedTime)}`)
                .then(response => response.json())
                .then(data => {
                    clientLastCheckedTime = data.timestamp;
                    
                    if (data.messages && data.messages.length > 0) {
                        // Play sound if enabled
                        if (window.clientSoundsEnabled) {
                            const audio = new Audio('/sounds/reception.wav');
                            audio.volume = 0.25;
                            audio.play().catch(() => {});
                        }

                        const isChatActive = chatOpen || window.location.pathname.includes('/client/messages');

                        if (isChatActive) {
                            if (chatOpen && typeof loadChatMessages === 'function') {
                                loadChatMessages();
                            }
                            if (window.location.pathname.includes('/client/messages') && typeof refreshClientChat === 'function') {
                                refreshClientChat();
                            }
                        } else {
                            data.messages.forEach(msg => {
                                showClientToast(msg);
                            });
                        }
                    }

                    const badge = document.getElementById('chatBadge');
                    if (badge) {
                        const count = data.unread_count;
                        if (count > 0 && !chatOpen) {
                            badge.textContent = count > 99 ? '99+' : count;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(err => console.error('Error polling client messages:', err));
        }

        if ({{ session('portal_client_id') ? 'true' : 'false' }}) {
            setInterval(pollClientMessages, 15000);
        }
    </script>
    @endif

</body>
</html>