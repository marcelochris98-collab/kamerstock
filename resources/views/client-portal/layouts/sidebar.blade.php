<aside
    :class="sidebarCollapsed && !mobileSidebarOpen ? 'w-20' : 'w-56'"
    class="bg-slate-900 flex flex-col flex-shrink-0 h-screen transition-all duration-300 overflow-hidden">

    @php
        $siteSettings = \App\Models\Setting::first();

        $itemClass = "flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition mb-1";
        $iconClass = "w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0";
        $subLinkClass = "flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition";
    @endphp

    {{-- Logo --}}
    <div class="flex items-center gap-2.5 px-5 h-14 border-b border-slate-800 flex-shrink-0">
        @if($siteSettings && $siteSettings->logo)
            <img src="{{ asset('storage/' . $siteSettings->logo) }}" alt="Logo" class="w-7 h-7 rounded-lg object-cover flex-shrink-0">
        @else
            <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center font-black text-sm text-slate-950 flex-shrink-0">
                {{ $siteSettings ? strtoupper(substr($siteSettings->shop_name, 0, 1)) : 'K' }}
            </div>
        @endif

        <div x-show="!sidebarCollapsed || mobileSidebarOpen" x-transition class="min-w-0">
            <p class="text-sm font-bold text-white leading-none truncate">{{ $siteSettings->shop_name ?? 'KamerStock' }}</p>
            <p class="text-xs text-slate-500 leading-none mt-0.5 truncate">Quincaillerie</p>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto" x-data="{ open: null }">

        <p x-show="!sidebarCollapsed || mobileSidebarOpen" class="text-xs font-semibold text-slate-600 uppercase tracking-widest px-2 mb-3">
            Menu
        </p>

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            title="Tableau de bord"
            class="{{ $itemClass }} {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" x-transition class="flex-1">Tableau de bord</span>
        </a>

        {{-- Ventes --}}
        @if(auth()->user()->hasPermission('sales.view') || auth()->user()->hasPermission('sales.create'))
        <div class="mb-1">
            <button @click="if (sidebarCollapsed && !mobileSidebarOpen) { window.location='{{ route('sales.index') }}' } else { open = open === 'ventes' ? null : 'ventes' }"
                title="Ventes"
                class="w-full {{ $itemClass }} {{ request()->routeIs('sales.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="{{ $iconClass }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1 text-left">Ventes</span>
                <svg x-show="!sidebarCollapsed || mobileSidebarOpen" class="w-3 h-3 transition-transform duration-200" :class="open === 'ventes' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div x-show="(!sidebarCollapsed || mobileSidebarOpen) && open === 'ventes'" x-transition
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                @if(auth()->user()->hasPermission('sales.create'))
                <a href="{{ route('sales.create') }}" class="{{ $subLinkClass }} {{ request()->routeIs('sales.create') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Nouvelle vente
                </a>
                @endif
                @if(auth()->user()->hasPermission('sales.view'))
                <a href="{{ route('sales.index') }}" class="{{ $subLinkClass }} {{ request()->routeIs('sales.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Historique
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Crédits --}}
        <div class="mb-1">
            <button @click="if (sidebarCollapsed && !mobileSidebarOpen) { window.location='{{ route('credits.index') }}' } else { open = open === 'credits' ? null : 'credits' }"
                title="Crédits"
                class="w-full {{ $itemClass }} {{ request()->routeIs('credits.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="{{ $iconClass }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1 text-left">Crédits</span>
                <svg x-show="!sidebarCollapsed || mobileSidebarOpen" class="w-3 h-3 transition-transform duration-200" :class="open === 'credits' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div x-show="(!sidebarCollapsed || mobileSidebarOpen) && open === 'credits'" x-transition
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('credits.index') }}" class="{{ $subLinkClass }} {{ request()->routeIs('credits.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Liste des crédits
                </a>
                <a href="{{ route('credits.payments.history') }}" class="{{ $subLinkClass }} {{ request()->routeIs('credits.payments.history') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Remboursements
                </a>
            </div>
        </div>

        {{-- Catalogue --}}
        @if(auth()->user()->hasPermission('products.view'))
        <div class="mb-1">
            <button @click="if (sidebarCollapsed && !mobileSidebarOpen) { window.location='{{ route('products.index') }}' } else { open = open === 'catalogue' ? null : 'catalogue' }"
                title="Catalogue"
                class="w-full {{ $itemClass }} {{ request()->routeIs('products.*', 'categories.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="{{ $iconClass }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1 text-left">Catalogue</span>
                <svg x-show="!sidebarCollapsed || mobileSidebarOpen" class="w-3 h-3 transition-transform duration-200" :class="open === 'catalogue' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div x-show="(!sidebarCollapsed || mobileSidebarOpen) && open === 'catalogue'" x-transition
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('products.index') }}" class="{{ $subLinkClass }} {{ request()->routeIs('products.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Tous les produits
                </a>
                @if(auth()->user()->hasPermission('products.create'))
                <a href="{{ route('products.create') }}" class="{{ $subLinkClass }} {{ request()->routeIs('products.create') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Ajouter un produit
                </a>
                @endif
                @if(auth()->user()->hasPermission('categories.manage'))
                <a href="{{ route('categories.index') }}" class="{{ $subLinkClass }} {{ request()->routeIs('categories.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Catégories
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Stock --}}
        @if(auth()->user()->hasPermission('stock.view'))
        <a href="{{ route('stock.index') }}"
            title="Stock"
            class="{{ $itemClass }} {{ request()->routeIs('stock.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1">Stock</span>
        </a>
        @endif

        {{-- Fournisseurs --}}
        @if(auth()->user()->hasPermission('suppliers.view'))
        <div class="mb-1">
            <button @click="if (sidebarCollapsed && !mobileSidebarOpen) { window.location='{{ route('suppliers.index') }}' } else { open = open === 'fournisseurs' ? null : 'fournisseurs' }"
                title="Fournisseurs"
                class="w-full {{ $itemClass }} {{ request()->routeIs('suppliers.*', 'purchases.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="{{ $iconClass }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1 text-left">Fournisseurs</span>
                <svg x-show="!sidebarCollapsed || mobileSidebarOpen" class="w-3 h-3 transition-transform duration-200" :class="open === 'fournisseurs' ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            <div x-show="(!sidebarCollapsed || mobileSidebarOpen) && open === 'fournisseurs'" x-transition
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('suppliers.index') }}" class="{{ $subLinkClass }} {{ request()->routeIs('suppliers.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Liste fournisseurs
                </a>
                <a href="{{ route('purchases.index') }}" class="{{ $subLinkClass }} {{ request()->routeIs('purchases.index', 'purchases.show') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Achats fournisseurs
                </a>
                <a href="{{ route('purchases.debts') }}" class="{{ $subLinkClass }} {{ request()->routeIs('purchases.debts') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Dettes fournisseurs
                </a>
                <a href="{{ route('purchases.payments-history') }}" class="{{ $subLinkClass }} {{ request()->routeIs('purchases.payments-history') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Paiements fournisseurs
                </a>
                <a href="{{ route('purchases.dashboard') }}" class="{{ $subLinkClass }} {{ request()->routeIs('purchases.dashboard') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Dashboard achats
                </a>
            </div>
        </div>
        @endif

        {{-- Clients --}}
        @if(auth()->user()->hasPermission('clients.view'))
        <a href="{{ route('clients.index') }}"
            title="Clients"
            class="{{ $itemClass }} {{ request()->routeIs('clients.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1">Clients</span>
        </a>
        @endif

        {{-- Administration --}}
        @if(auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage') || auth()->user()->hasPermission('settings.manage'))
        <p x-show="!sidebarCollapsed || mobileSidebarOpen" class="text-xs font-semibold text-slate-600 uppercase tracking-widest px-2 mt-5 mb-3">
            Administration
        </p>

        @if(auth()->user()->hasPermission('users.manage'))
        <a href="{{ route('admin.users.index') }}"
            title="Utilisateurs"
            class="{{ $itemClass }} {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1">Utilisateurs</span>
        </a>
        @endif

        @if(auth()->user()->hasPermission('roles.manage'))
        <a href="{{ route('admin.roles.index') }}"
            title="Rôles"
            class="{{ $itemClass }} {{ request()->routeIs('admin.roles.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1">Rôles</span>
        </a>
        @endif

        @if(auth()->user()->hasPermission('settings.manage'))
        <a href="{{ route('admin.settings.index') }}"
            title="Paramètres"
            class="{{ $itemClass }} {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1">Paramètres</span>
        </a>

        <a href="{{ route('settings.credit.edit') }}"
            title="Crédit auto"
            class="{{ $itemClass }} {{ request()->routeIs('settings.credit.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="{{ $iconClass }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <span x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1">Crédit auto</span>
        </a>
        @endif
        @endif

    </nav>

    {{-- User --}}
    <div class="border-t border-slate-800 px-4 py-3 flex-shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center text-slate-950 font-bold text-xs flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div x-show="!sidebarCollapsed || mobileSidebarOpen" class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-300 truncate leading-none">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-600 truncate leading-none mt-0.5">{{ auth()->user()->role_label }}</p>
            </div>

            <form x-show="!sidebarCollapsed || mobileSidebarOpen" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Déconnexion"
                    class="text-slate-600 hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</aside>