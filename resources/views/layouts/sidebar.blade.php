<aside class="fixed md:static inset-y-0 left-0 z-40 w-56 bg-slate-900 flex flex-col flex-shrink-0 transform md:transform-none transition-transform duration-200"
    :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

    {{-- Logo --}}
    <div class="flex items-center gap-2.5 px-5 h-14 border-b border-slate-800">
        @php
            $siteSettings = \App\Models\Setting::first();
        @endphp

        @if($siteSettings && $siteSettings->logo)
            <img src="{{ asset('storage/' . $siteSettings->logo) }}" alt="Logo" class="w-7 h-7 rounded-lg object-cover flex-shrink-0">
        @else
            <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center font-black text-sm text-slate-950 flex-shrink-0">
                {{ $siteSettings ? strtoupper(substr($siteSettings->shop_name, 0, 1)) : 'K' }}
            </div>
        @endif

        <div>
            <p class="text-sm font-bold text-white leading-none">{{ $siteSettings->shop_name ?? 'KamerStock' }}</p>
            <p class="text-xs text-slate-500 leading-none mt-0.5">Quincaillerie</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto"
        x-data="{ open: '{{ request()->routeIs('sales.*') || request()->routeIs('quotes.*') ? 'ventes' : (request()->routeIs('products.*') || request()->routeIs('categories.*') ? 'catalogue' : (request()->routeIs('purchases.*') || request()->routeIs('advanced_purchases.*') ? 'achats' : (request()->routeIs('clients.*') || request()->routeIs('credits.*') || request()->routeIs('admin.crm_messages.*') ? 'crm' : (request()->routeIs('admin.*') || request()->routeIs('settings.*') ? 'admin' : (request()->routeIs('stock.*') ? 'stock' : ''))))) }}' }">

        <p class="text-xs font-semibold text-slate-600 uppercase tracking-widest px-2 mb-3">Menu Principal</p>

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

        {{-- Ventes & Devis --}}
        @if(auth()->user()->hasPermission('sales.view') || auth()->user()->hasPermission('sales.create'))
        <div class="mb-1">
            <button @click="open = open === 'ventes' ? '' : 'ventes'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('sales.*', 'quotes.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Ventes & Devis</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'ventes' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'ventes'" x-cloak
                class="mt-1 ml-4 pl-3 border-l border-slate-850 space-y-0.5">
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
                    Historique ventes
                </a>
                @endif
                <a href="{{ route('quotes.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('quotes.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Devis & Proformas
                </a>
            </div>
        </div>
        @endif

        {{-- Catalogue --}}
        @if(auth()->user()->hasPermission('products.view'))
        <div class="mb-1">
            <button @click="open = open === 'catalogue' ? '' : 'catalogue'"
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
            <div x-show="open === 'catalogue'" x-cloak
                class="mt-1 ml-4 pl-3 border-l border-slate-850 space-y-0.5">
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
                    Catégories
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Achats --}}
        @if(auth()->user()->hasPermission('suppliers.view') || auth()->user()->hasPermission('purchases.view'))
        <div class="mb-1">
            <button @click="open = open === 'achats' ? '' : 'achats'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('purchases.*', 'advanced_purchases.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Approvisionnement</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'achats' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'achats'" x-cloak
                class="mt-1 ml-4 pl-3 border-l border-slate-850 space-y-0.5">
                <a href="{{ route('purchases.dashboard') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('purchases.dashboard') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Dashboard achats
                </a>
                <a href="{{ route('advanced_purchases.orders.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('advanced_purchases.orders.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Bons de Commande
                </a>
                @if(Route::has('advanced_purchases.receptions.index'))
                <a href="{{ route('advanced_purchases.receptions.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('advanced_purchases.receptions.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Réceptions livraisons
                </a>
                @endif
                <a href="{{ route('advanced_purchases.returns.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('advanced_purchases.returns.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Retours Fournisseurs
                </a>
                <a href="{{ route('advanced_purchases.suggestions') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('advanced_purchases.suggestions') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Suggestions de stock
                </a>
                <a href="{{ route('purchases.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('purchases.index') || request()->routeIs('purchases.show') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Factures d'Achat
                </a>
                <a href="{{ route('suppliers.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('suppliers.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Fiche Fournisseurs
                </a>
                <a href="{{ route('purchases.debts') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('purchases.debts') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Dettes Fournisseurs
                </a>
                <a href="{{ route('purchases.payments-history') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('purchases.payments-history') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Historique paiements
                </a>
            </div>
        </div>
        @endif

        {{-- CRM & Relation Client --}}
        @if(auth()->user()->hasPermission('clients.view'))
        <div class="mb-1">
            <button @click="open = open === 'crm' ? '' : 'crm'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('clients.*', 'credits.*', 'admin.crm_messages.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">CRM & Clients</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'crm' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'crm'" x-cloak
                class="mt-1 ml-4 pl-3 border-l border-slate-850 space-y-0.5">
                <a href="{{ route('clients.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('clients.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Fiches Clients
                </a>
                <a href="{{ route('credits.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('credits.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Crédits Clients
                </a>
                <a href="{{ route('credits.payments.history') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('credits.payments.history') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Remboursements
                </a>
            </div>
        </div>
        @endif

        {{-- Stock mouvements --}}
        @if(auth()->user()->hasPermission('stock.view'))
        <div class="mb-1">
            <button @click="open = open === 'stock' ? '' : 'stock'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('stock.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Gestion Stock</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'stock' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'stock'" x-cloak
                class="mt-1 ml-4 pl-3 border-l border-slate-850 space-y-0.5">
                <a href="{{ route('stock.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('stock.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Tableau de Bord
                </a>
                <a href="{{ route('stock.history') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('stock.history') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Historique
                </a>
            </div>
        </div>
        @endif

        {{-- Rapports --}}
        @if(auth()->user()->hasPermission('reports.view'))
        <a href="{{ route('reports.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition mb-1
            {{ request()->routeIs('reports.index') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/>
                </svg>
            </span>
            <span class="flex-1">Rapports Financiers</span>
        </a>
        @endif

        {{-- ADMINISTRATION --}}
        @if(auth()->user()->hasPermission('users.manage') || auth()->user()->hasPermission('roles.manage') || auth()->user()->hasPermission('settings.manage') || auth()->user()->hasPermission('logs.view'))
        <p class="text-xs font-semibold text-slate-600 uppercase tracking-widest px-2 mt-5 mb-3">Administration</p>

        <div class="mb-1">
            <button @click="open = open === 'admin' ? '' : 'admin'"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition
                {{ request()->routeIs('admin.*', 'settings.credit.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <span class="w-6 h-6 rounded-md flex items-center justify-center bg-slate-800 flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <span class="flex-1 text-left">Configuration</span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open === 'admin' ? 'rotate-90' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
            <div x-show="open === 'admin'" x-cloak
                class="mt-1 ml-4 pl-3 border-l border-slate-850 space-y-0.5">
                @if(auth()->user()->hasPermission('users.manage'))
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('admin.users.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Utilisateurs
                </a>
                @endif
                @if(auth()->user()->hasPermission('roles.manage'))
                <a href="{{ route('admin.roles.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('admin.roles.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Rôles & Permissions
                </a>
                @endif
                @if(auth()->user()->hasPermission('settings.manage'))
                <a href="{{ route('admin.settings.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('admin.settings.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Paramètres Shop
                </a>
                <a href="{{ route('settings.credit.edit') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('settings.credit.edit') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Crédit Automatique
                </a>
                <a href="{{ route('admin.backups.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('admin.backups.index') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Sauvegardes DB
                </a>
                @endif
                @if(auth()->user()->hasPermission('logs.view'))
                <a href="{{ route('admin.audit-logs.index') }}"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-xs transition
                    {{ request()->routeIs('admin.audit-logs.*') ? 'text-amber-400 font-medium' : 'text-slate-500 hover:text-white' }}">
                    Journal d'audit
                </a>
                @endif
            </div>
        </div>
        @endif

    </nav>

    {{-- User Footer --}}
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
