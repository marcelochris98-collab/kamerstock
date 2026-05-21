<header class="h-12 bg-white border-b border-slate-100 px-6 flex items-center justify-between flex-shrink-0">

    <div class="flex items-center gap-2">
        <span class="text-xs text-slate-400">KamerStock</span>
        <span class="text-slate-200">/</span>
        <span class="text-sm font-semibold text-slate-800">@yield('page-title', 'Dashboard')</span>
    </div>

    <div class="flex items-center gap-3">

        <span class="text-xs text-slate-400 hidden md:block">
            {{ now()->locale('fr')->isoFormat('ddd D MMM YYYY') }}
        </span>

        {{-- Alertes stock --}}
        @php $alertCount = \App\Models\Product::where('is_active', true)->whereRaw('quantity <= alert_threshold')->count(); @endphp

        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open"
                class="relative w-8 h-8 flex items-center justify-center rounded-lg border border-slate-100 bg-white hover:bg-slate-50 transition text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($alertCount > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold border-2 border-white">
                        {{ $alertCount }}
                    </span>
                @endif
            </button>

            <div x-show="open" @click.outside="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 top-10 w-72 bg-white border border-slate-100 rounded-xl shadow-lg z-50">
                <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">Alertes stock</span>
                    <span class="text-xs text-slate-400">{{ $alertCount }} produit(s)</span>
                </div>
                @if($alertCount > 0)
                    @foreach(\App\Models\Product::where('is_active', true)->whereRaw('quantity <= alert_threshold')->limit(5)->get() as $p)
                    <div class="px-4 py-3 border-b border-slate-50 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-slate-700">{{ $p->name }}</p>
                            <p class="text-xs text-slate-400">{{ $p->quantity }} unité(s) restante(s)</p>
                        </div>
                        <span class="text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded">Bas</span>
                    </div>
                    @endforeach
                @else
                    <div class="px-4 py-4 text-center text-xs text-slate-400">Aucune alerte</div>
                @endif
                <div class="px-4 py-2 text-center">
                    <a href="{{ route('products.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Voir tous les produits</a>
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
               <!-- <p class="text-xs text-slate-400 leading-none mt-0.5">{{ auth()->user()->role_label }}</p>-->
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
