<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Connexion') — KamerStock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        <aside class="relative flex min-h-[430px] flex-col overflow-hidden border-b border-slate-800 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-950 px-7 py-7 sm:px-10 lg:min-h-screen lg:border-b-0 lg:border-r lg:px-12 lg:py-10">
            <div class="pointer-events-none absolute -bottom-28 -right-20 text-slate-800/30" aria-hidden="true">
                <svg class="h-96 w-96" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.2c0 4.5 3.2 8.6 7.5 9.8 4.3-1.2 7.5-5.3 7.5-9.8V6L12 3Z"/>
                </svg>
            </div>

            <a href="{{ url('/') }}" class="relative z-10 inline-flex w-fit items-center gap-3" aria-label="KamerStock, accueil">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-lg font-extrabold text-slate-950 shadow-lg shadow-amber-950/30">K</span>
                <span>
                    <span class="block text-sm font-extrabold tracking-[0.18em] text-white">KAMERSTOCK</span>
                    <span class="mt-0.5 block text-[10px] font-medium uppercase tracking-[0.22em] text-slate-500">Gestion commerciale</span>
                </span>
            </a>

            <div class="relative z-10 my-auto max-w-xl py-12 lg:py-16">
                <span class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-white/[0.04] px-3 py-1.5 text-xs font-medium text-slate-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    La gestion de votre commerce, simplement
                </span>

                <h1 class="mt-7 text-4xl font-bold leading-tight tracking-tight text-white sm:text-5xl">
                    Votre commerce,
                    <span class="block text-slate-400">enfin sous contrôle.</span>
                </h1>
                <p class="mt-5 max-w-lg text-sm leading-6 text-slate-400 sm:text-base">
                    Stocks, ventes, achats et clients réunis dans un espace clair, accessible et réservé à votre entreprise.
                </p>

                <ul class="mt-9 space-y-5">
                    <li class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-white/[0.04] text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20 7-8-4-8 4m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-100">Stock et produits en temps réel</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Suivez vos articles, les mouvements et les alertes de stock.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-white/[0.04] text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l2.4 12.1a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 8H6m4 13h.01M18 21h.01"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-100">Ventes, achats et clients réunis</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Retrouvez les opérations et les informations utiles au même endroit.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-700 bg-white/[0.04] text-amber-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.2c0 4.5 3.2 8.6 7.5 9.8 4.3-1.2 7.5-5.3 7.5-9.8V6L12 3Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-slate-100">Un espace dédié à votre entreprise</span>
                            <span class="mt-1 block text-xs leading-5 text-slate-500">Vos équipes travaillent dans un espace de données séparé.</span>
                        </span>
                    </li>
                </ul>
            </div>

            <p class="relative z-10 text-[11px] text-slate-600">© {{ date('Y') }} KamerStock. Tous droits réservés.</p>
        </aside>

        <main class="flex min-h-[620px] items-center justify-center bg-slate-950 px-5 py-12 sm:px-8 lg:min-h-screen">
            <div class="w-full max-w-md">
                <div class="mb-7 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-lg font-extrabold text-slate-950 shadow-lg shadow-amber-950/30">K</span>
                    <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Espace sécurisé</span>
                </div>

                <h2 class="text-2xl font-bold tracking-tight text-white">@yield('auth-heading')</h2>
                <p class="mt-2 text-sm leading-5 text-slate-400">@yield('auth-description')</p>

                <nav class="mt-6 grid grid-cols-2 gap-1 rounded-xl border border-slate-800 bg-slate-900 p-1" aria-label="Connexion ou création d’entreprise">
                    <a href="{{ route('login', request()->query()) }}"
                       @class([
                           'rounded-lg px-3 py-2.5 text-center text-xs font-semibold transition',
                           'bg-amber-500 text-slate-950 shadow-sm' => request()->routeIs('login'),
                           'text-slate-400 hover:text-white' => !request()->routeIs('login'),
                       ])
                       @if(request()->routeIs('login')) aria-current="page" @endif>
                        Se connecter
                    </a>
                    <a href="{{ route('register') }}"
                       @class([
                           'rounded-lg px-3 py-2.5 text-center text-xs font-semibold transition',
                           'bg-amber-500 text-slate-950 shadow-sm' => request()->routeIs('register'),
                           'text-slate-400 hover:text-white' => !request()->routeIs('register'),
                       ])
                       @if(request()->routeIs('register')) aria-current="page" @endif>
                        Créer une entreprise
                    </a>
                </nav>

                @if($errors->any())
                    <div class="mt-5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3" role="alert">
                        <p class="text-sm text-rose-300">{{ $errors->first() }}</p>
                    </div>
                @endif

                @if(session('status'))
                    <div class="mt-5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3" role="status">
                        <p class="text-sm text-emerald-300">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="mt-6 rounded-2xl border border-slate-800 bg-slate-900/70 p-5 shadow-2xl shadow-black/20 sm:p-7">
                    @yield('auth-content')
                </div>

                <div class="mt-6 flex items-center justify-center gap-2 text-xs text-slate-500">
                    <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4.5 6v5.2c0 4.5 3.2 8.6 7.5 9.8 4.3-1.2 7.5-5.3 7.5-9.8V6L12 3Z"/>
                    </svg>
                    Accès réservé à votre espace entreprise
                </div>
                <div class="mt-5 text-center">
                    <a href="{{ url('/') }}" class="text-xs text-slate-500 transition hover:text-amber-300">Retour à l’accueil</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
