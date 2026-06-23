<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamerStock — Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-sm px-6">

        {{-- Logo --}}
        <div class="flex items-center justify-center gap-3 mb-8">
            <div class="w-9 h-9 bg-amber-500 rounded-lg flex items-center justify-center font-black text-slate-950 text-lg">K</div>
            <div>
                <p class="text-base font-bold text-white leading-none">KamerStock</p>
                <p class="text-xs text-slate-500 leading-none mt-0.5">{{ app(\App\Services\BusinessTypeService::class)->dashboardTitle() }}</p>
            </div>
        </div>

        {{-- Card --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">

            <h1 class="text-sm font-semibold text-white mb-1">Connexion</h1>
            <p class="text-xs text-slate-500 mb-6">Entrez vos identifiants pour accéder au système.</p>

            {{-- Erreurs --}}
            @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 mb-4">
                <p class="text-xs text-red-400">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500
                        focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                        placeholder="admin@kamerstock.cm">
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">Mot de passe</label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500
                        focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                        placeholder="••••••••">
                </div>

                <button type="submit"
                    class="w-full py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-sm rounded-lg transition">
                    Se connecter
                </button>
            </form>
        </div>

        <div class="text-center mt-6 flex flex-col items-center gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-white transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à l'accueil
            </a>
            
            <a href="{{ route('landlord.login') }}" class="inline-flex items-center gap-1 text-[10px] text-slate-700 hover:text-slate-500 transition select-none">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                Console Administration
            </a>
            
            <p class="text-[10px] text-slate-600 uppercase tracking-wider">
                KamerStock v1.0 — Douala, Cameroun
            </p>
        </div>
    </div>

</body>
</html>