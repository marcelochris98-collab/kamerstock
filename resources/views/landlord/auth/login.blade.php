<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KamerStock Landlord | Connexion</title>
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
        <div class="mx-auto w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl text-white shadow-lg shadow-indigo-600/30">
            K
        </div>
        <h2 class="mt-6 text-2xl font-black tracking-tight text-white">KamerStock Landlord</h2>
        <p class="mt-2 text-xs text-slate-400 font-semibold uppercase tracking-wider">Espace propriétaire de la plateforme</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-slate-800 border border-slate-700/60 py-8 px-4 shadow-xl shadow-slate-950/40 rounded-2xl sm:px-10">
            
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-950/40 border border-red-800 text-red-400 rounded-xl text-xs font-semibold">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('landlord.login.store') }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wide mb-1.5">Adresse e-mail</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            class="block w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all duration-150">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wide mb-1.5">Mot de passe</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="block w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all duration-150">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-slate-800">
                        <label for="remember" class="ml-2 block text-xs text-slate-400 font-semibold">Se souvenir de moi</label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-md text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150">
                        Connexion Super Admin
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
