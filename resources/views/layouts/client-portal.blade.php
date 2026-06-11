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

</body>
</html>