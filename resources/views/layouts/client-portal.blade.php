<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamerStock Client | @yield('title', 'Portail')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex flex-col">

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
    @php
        $unreadMessagesCount = \App\Models\ClientMessage::where('client_id', session('portal_client_id'))
            ->whereNotNull('user_id')
            ->whereNull('read_at')
            ->count();
    @endphp
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
                class="px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap flex items-center gap-1.5 {{ request()->routeIs('client.portal.messages') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600' }}">
                <span>Messages</span>
                <span id="navClientChatBadge" class="bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $unreadMessagesCount > 0 ? '' : 'hidden' }}">
                    {{ $unreadMessagesCount }}
                </span>
            </a>
        </div>
    </div>
    @endif

    <main class="max-w-5xl mx-auto px-4 py-6 flex-1 w-full">
        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="py-6 border-t border-slate-200 bg-white mt-auto">
        <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <div>
                <p>&copy; {{ date('Y') }} <span class="font-bold text-slate-600">KamerStock</span>. Tous droits réservés.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <p class="font-medium text-slate-500">Portail Client Sécurisé</p>
            </div>
        </div>
    </footer>

    @if(session('portal_client_id'))
    <script>
        @if(session('portal_client_id'))
        @php
            $clientModel = \App\Models\Client::find(session('portal_client_id'));
        @endphp
        window.clientSoundsEnabled = {{ ($clientModel && $clientModel->sounds_enabled) ? 'true' : 'false' }};
        @else
        window.clientSoundsEnabled = false;
        @endif

        let clientLastCheckedTime = '{{ now()->toIso8601String() }}';

        function showClientToast(msg) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 z-[9999] bg-white shadow-lg rounded-xl px-4 py-3 max-w-sm border border-emerald-100 transition-all duration-300';
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

                        const isChatActive = window.location.pathname.includes('/client/messages');

                        if (isChatActive) {
                            if (typeof refreshClientChat === 'function') {
                                refreshClientChat();
                            }
                        } else {
                            data.messages.forEach(msg => {
                                showClientToast(msg);
                            });
                        }
                    }

                    const badge = document.getElementById('navClientChatBadge');
                    if (badge) {
                        const count = data.unread_count;
                        if (count > 0 && !window.location.pathname.includes('/client/messages')) {
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