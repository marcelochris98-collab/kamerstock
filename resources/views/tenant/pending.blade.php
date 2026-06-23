<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace en préparation | KamerStock</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-200">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Spinner premium -->
        <div class="mx-auto w-16 h-16 bg-slate-800 border border-slate-700/80 rounded-2xl flex items-center justify-center shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin m-2"></div>
            <span class="font-black text-xl text-white z-10">K</span>
        </div>

        <h2 class="mt-6 text-2xl font-black tracking-tight text-white">Espace boutique en préparation</h2>
        <p class="mt-2 text-xs text-slate-400 font-semibold uppercase tracking-wider">
            Boutique : <span class="text-indigo-400 font-bold">{{ $tenant_name }}</span>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-slate-800/80 backdrop-blur-md border border-slate-700/60 py-8 px-4 shadow-xl shadow-slate-950/40 rounded-2xl sm:px-10 text-center">
            
            <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/30 rounded-xl flex items-center justify-center mx-auto mb-4 text-amber-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <p class="text-sm font-semibold text-slate-350">
                Votre espace KamerStock est en cours de préparation. Veuillez patienter ou contacter l’administrateur de la plateforme.
            </p>

            <div class="mt-6 border-t border-slate-700/60 pt-4 flex flex-col gap-2.5">
                <div class="flex justify-between items-center text-[10px] px-2 text-slate-500 font-semibold">
                    <span>STATUT TECHNIQUE</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-900 border border-slate-700 text-slate-400 capitalize font-mono">
                        {{ $tenant ? $tenant->provisioning_status : 'prepared' }}
                    </span>
                </div>
            </div>

            <div class="mt-8">
                <a href="{{ url('/') }}" class="w-full flex justify-center py-2 px-4 border border-slate-700 rounded-xl text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-700 transition">
                    Retour à l'accueil
                </a>
            </div>

        </div>
    </div>
</body>
</html>
