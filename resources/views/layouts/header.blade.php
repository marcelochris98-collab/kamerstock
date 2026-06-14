<header class="h-12 bg-white border-b border-slate-100 px-6 flex items-center justify-between flex-shrink-0">

    <div class="flex items-center gap-2">
        <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="md:hidden text-slate-500 hover:text-slate-800 focus:outline-none mr-2" title="Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <span class="text-xs text-slate-400">KamerStock</span>
        <span class="text-slate-200">/</span>
        <span class="text-sm font-semibold text-slate-800">@yield('page-title', 'Dashboard')</span>
    </div>

    {{-- Spotlight Search --}}
    <div class="flex-1 max-w-xs md:max-w-md mx-6 relative" x-data="globalSearch()" @click.outside="open = false" @keydown.escape="open = false">
        <div class="relative">
            <input type="text" x-model="query" @input.debounce.300ms="performSearch()" @focus="open = true"
                placeholder="Recherche rapide... (Ctrl + K)"
                class="w-full px-3 py-1.5 pl-8 border border-slate-200 rounded-lg text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-slate-400 focus:bg-white transition"
                @keydown.window.prevent.ctrl.k="focusSearch($event)">
            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <div x-show="open && hasResults()"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="absolute left-0 right-0 top-10 bg-white border border-slate-150 rounded-xl shadow-xl z-50 overflow-hidden max-h-96 overflow-y-auto">
            <template x-for="(items, category) in results" :key="category">
                <div>
                    <div class="px-3 py-1 bg-slate-50 border-b border-slate-100 text-[10px] font-bold text-slate-400 tracking-wider uppercase" x-text="category"></div>
                    <div class="divide-y divide-slate-50">
                        <template x-for="item in items" :key="item.url">
                            <a :href="item.url" class="block px-3 py-2 hover:bg-slate-50 transition">
                                <p class="text-xs font-semibold text-slate-700" x-text="item.title"></p>
                                <p class="text-[10px] text-slate-400 mt-0.5" x-text="item.subtext"></p>
                            </a>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
    function globalSearch() {
        return {
            query: '',
            open: false,
            results: {},
            performSearch() {
                if (this.query.length < 2) {
                    this.results = {};
                    return;
                }
                fetch(`/global-search?q=${encodeURIComponent(this.query)}`)
                    .then(response => response.json())
                    .then(data => {
                        this.results = data.results;
                        this.open = true;
                    });
            },
            hasResults() {
                return Object.keys(this.results).length > 0;
            },
            focusSearch(e) {
                const input = this.$el.querySelector('input');
                if (input) input.focus();
            }
        }
    }
    </script>

    <div class="flex items-center gap-3">

        <span class="text-xs text-slate-400 hidden md:block">
            {{ now()->locale('fr')->isoFormat('ddd D MMM YYYY') }}
        </span>

        @php
            $stockAlertCount = \App\Models\Product::where('is_active', true)
                ->whereRaw('quantity <= alert_threshold')
                ->count();

            $unreadNotifications = \App\Models\Notification::where('is_read', false)
                ->latest()
                ->limit(5)
                ->get();

            $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();

            $totalAlertCount = $stockAlertCount + $unreadNotificationsCount;
        @endphp

        {{-- Message Icon --}}
        @if(auth()->user()->hasPermission('crm.messages'))
            @php
                $unreadCrmMessagesCount = \App\Models\ClientMessage::whereNotNull('client_id')
                    ->whereNull('user_id')
                    ->whereNull('read_at')
                    ->count();
            @endphp
            <div class="relative">
                <a href="{{ route('admin.crm_messages.index') }}"
                    class="relative w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 bg-white hover:bg-slate-50 transition text-slate-500"
                    title="Messages Clients">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span id="headerCrmChatBadge" class="absolute -top-1 -right-1 bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $unreadCrmMessagesCount > 0 ? '' : 'hidden' }}">
                        {{ $unreadCrmMessagesCount }}
                    </span>
                </a>
            </div>
        @endif

        {{-- Cloche notifications --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                class="relative w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 bg-white hover:bg-slate-50 transition text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>

                @if($totalAlertCount > 0)
                    <span class="absolute -top-1 -right-1 min-w-4 h-4 px-1 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold border-2 border-white">
                        {{ $totalAlertCount > 99 ? '99+' : $totalAlertCount }}
                    </span>
                @endif
            </button>

            <div x-show="open" @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 top-10 w-80 bg-white border border-slate-100 rounded-xl shadow-lg z-50 overflow-hidden">

                <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Notifications</span>
                    <span class="text-xs text-slate-400">{{ $totalAlertCount }} alerte(s)</span>
                </div>

                {{-- Notifications système --}}
                @forelse($unreadNotifications as $notification)
                    <a href="{{ route('notifications.read', $notification) }}"
                        class="block px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition">
                        <p class="text-xs font-semibold text-slate-700">{{ $notification->title }}</p>
                        @if($notification->message)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $notification->message }}</p>
                        @endif
                        <p class="text-[10px] text-slate-300 mt-1">
                            {{ $notification->created_at->format('d/m/Y H:i') }}
                        </p>
                    </a>
                @empty
                    @if($stockAlertCount === 0)
                        <div class="px-4 py-4 text-center text-xs text-slate-400">
                            Aucune notification
                        </div>
                    @endif
                @endforelse

                {{-- Alertes stock --}}
                @if($stockAlertCount > 0)
                    <div class="px-4 py-2 bg-slate-50 border-b border-slate-100">
                        <span class="text-xs font-semibold text-slate-600">Alertes stock</span>
                    </div>

                    @foreach(\App\Models\Product::where('is_active', true)->whereRaw('quantity <= alert_threshold')->limit(5)->get() as $p)
                    <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-700">{{ $p->name }}</p>
                            <p class="text-xs text-slate-400">{{ $p->quantity }} unité(s) restante(s)</p>
                        </div>
                        <span class="text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded">Bas</span>
                    </div>
                    @endforeach
                @endif

                <div class="px-4 py-2 flex items-center justify-between">
                    <a href="{{ route('notifications.index') }}" class="text-xs text-slate-500 hover:text-slate-700">
                        Voir toutes
                    </a>

                    <a href="{{ route('products.index') }}" class="text-xs text-slate-500 hover:text-slate-700">
                        Stocks
                    </a>
                </div>
            </div>
        </div>

        {{-- Profil --}}
        <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-lg">
            <div class="w-6 h-6 rounded bg-slate-800 flex items-center justify-center text-white font-semibold text-xs">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="hidden md:block">
                <p class="text-xs font-semibold text-slate-700 leading-none">{{ auth()->user()->name }}</p>
            </div>
        </div>

        {{-- Déconnexion --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Déconnexion"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 text-slate-400 hover:text-red-500 hover:border-red-100 hover:bg-red-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>

    </div>
</header>
