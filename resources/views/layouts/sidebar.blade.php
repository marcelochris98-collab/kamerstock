<aside class="w-56 bg-slate-900 flex flex-col flex-shrink-0">

    {{--  Logo  --}}
<div class="flex items-center gap-2.5 px-5 h-14 border-b border-slate-800">
    @php
        $siteSettings = \App\Models\Setting::first();
    @endphp

    @if($siteSettings && $siteSettings->logo)
        {{-- Affiche le logo uploade --}}
        <img src="{{ asset('storage/' . $siteSettings->logo) }}" alt="Logo" class="w-7 h-7 rounded-lg object-cover flex-shrink-0">
    @else
        {{-- Version par défaut si pas de logo --}}
        <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center font-black text-sm text-slate-950 flex-shrink-0">
            {{ $siteSettings ? strtoupper(substr($siteSettings->shop_name, 0, 1)) : 'K' }}
        </div>
    @endif

    <div>
        <p class="text-sm font-bold text-white leading-none">{{ $siteSettings->shop_name ?? 'KamerStock' }}</p>
        <p class="text-xs text-slate-500 leading-none mt-0.5">Quincaillerie</p>
    </div>
</div>
    {{-- Nav --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto" x-data="{ open: null }">

        {{-- MENU --}}
        <p class="text-xs font-semibold text-slate-600 uppercase tracking-widest px-2 mb-3">Menu</p>

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition mb-1
            {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </span>
            <span class="flex-1">Tableau de bord</span>
        </a>

        {{-- Ventes --}}
        @if(auth()->user()->hasPermission('sales.view') || auth()->user()->hasPermission('sales.create'))
        <div class="mb-1">
            <button @click="open = open === 'ventes' ? null : 'ventes'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('sales.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Ventes</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'ventes' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'ventes'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                @if(auth()->user()->hasPermission('sales.create'))
                <a href="{{ route('sales.create') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('sales.create') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Nouvelle vente
                </a>
                @endif
                @if(auth()->user()->hasPermission('sales.view'))
                <a href="{{ route('sales.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('sales.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Historique
                </a>
                @endif
            </div>
        </div>
        @endif
     <div class="mb-1">
    <button @click="open = open === 'credits' ? null : 'credits'"
        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
        {{ request()->routeIs('credits.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">

        <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>

        <span class="flex-1 text-left">Crédits</span>

        <svg class="w-3 h-3 transition-transform duration-200"
            :class="open === 'credits' ? 'rotate-90' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <div x-show="open === 'credits'"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">

        <a href="{{ route('credits.index') }}"
            class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
            {{ request()->routeIs('credits.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
            Liste des crédits
        </a>
        <a href="{{ route('credits.payments.history') }}"
    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
    {{ request()->routeIs('credits.payments.history') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
    remboursements
</a>
    </div>
</div>

        {{-- Catalogue --}}
        @if(auth()->user()->hasPermission('products.view'))
        <div class="mb-1">
            <button @click="open = open === 'catalogue' ? null : 'catalogue'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('products.*', 'categories.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Catalogue</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'catalogue' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'catalogue'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('products.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('products.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Tous les produits
                </a>
                @if(auth()->user()->hasPermission('products.create'))
                <a href="{{ route('products.create') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('products.create') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Ajouter un produit
                </a>
                @endif
                @if(auth()->user()->hasPermission('categories.manage'))
                <a href="{{ route('categories.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('categories.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Categories
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Stock --}}
        @if(auth()->user()->hasPermission('stock.view'))
        <div class="mb-1">
            <button @click="open = open === 'stock' ? null : 'stock'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('stock.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Stock</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'stock' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'stock'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('stock.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('stock.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Mouvements
                </a>
                @if(auth()->user()->hasPermission('stock.manage'))
                <a href="{{ route('stock.index') }}#nouveau"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs text-slate-500 hover:text-white transition">

                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Fournisseurs --}}
        @if(auth()->user()->hasPermission('suppliers.view'))
        <div class="mb-1">
            <button @click="open = open === 'fournisseurs' ? null : 'fournisseurs'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('suppliers.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Fournisseurs</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'fournisseurs' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'fournisseurs'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('suppliers.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('suppliers.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Liste fournisseurs
                </a>
                @if(auth()->user()->hasPermission('suppliers.manage'))
                <a href="{{ route('suppliers.index') }}#nouveau"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs text-slate-500 hover:text-white transition">

                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Clients --}}
        @if(auth()->user()->hasPermission('clients.view'))
        <div class="mb-1">
            <button @click="open = open === 'clients' ? null : 'clients'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('clients.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Clients</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'clients' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'clients'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('clients.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('clients.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Liste clients
                </a>
                @if(auth()->user()->hasPermission('clients.manage'))
                <a href="{{ route('clients.index') }}#nouveau"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs text-slate-500 hover:text-white transition">

                </a>
                @endif
            </div>
            {{--notification --}}
           <!-- <a href="{{ route('notifications.index') }}"
    class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
    {{ request()->routeIs('notifications.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">

    <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
           </span>
               <span class="flex-1 text-left">Notifications</span>
              @php
                $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
                     @endphp
                        @if($unreadNotificationsCount > 0)
                   <span class="px-1.5 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold">
                {{ $unreadNotificationsCount }}
              </span>
          @endif
         </a>-->

        </div>
        @endif

        {{-- ADMINISTRATION --}}
        @if(auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage') || auth()->user()->hasPermission('settings.manage'))
        <p class="text-xs font-semibold text-slate-600 uppercase tracking-widest px-2 mt-5 mb-3">Administration</p>

        @if(auth()->user()->hasPermission('users.manage'))
        <div class="mb-1">
            <button @click="open = open === 'users' ? null : 'users'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Utilisateurs</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'users' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'users'"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mt-1 ml-4 pl-3 border-l border-slate-800 space-y-0.5">
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('admin.users.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Liste utilisateurs
                </a>
            </div>
        </div>
        @endif

        @if(auth()->user()->hasPermission('roles.manage'))
        <a href="{{ route('admin.roles.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition mb-1
            {{ request()->routeIs('admin.roles.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </span>
            <span class="flex-1">Roles & Permissions</span>
        </a>
        @endif

       @if(auth()->user()->hasPermission('settings.manage'))
<a href="{{ route('admin.settings.index') }}"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition mb-1
    {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">

    <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
    </span>

    <span class="flex-1">Paramètres</span>
</a>

<a href="{{ route('settings.credit.edit') }}"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition mb-1
    {{ request()->routeIs('settings.credit.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">

    <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </span>

    <span class="flex-1">Crédit intelligent</span>
</a>
@endif
        @endif

    </nav>

    {{-- User --}}
    <div class="border-t border-slate-800 px-4 py-3">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center text-slate-950 font-bold text-xs flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-300 truncate leading-none">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-600 truncate leading-none mt-0.5">{{ auth()->user()->role_label }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Deconnexion"
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
