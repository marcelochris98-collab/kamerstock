@extends('layouts.auth')

@section('title', 'Connexion')
@section('auth-heading', 'Connexion')
@section('auth-description', 'Connectez-vous pour retrouver votre espace de gestion.')

@section('auth-content')
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-xs font-medium text-slate-300">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="vous@entreprise.com">
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-3">
                <label for="tenant" class="block text-xs font-medium text-slate-300">Identifiant de l’entreprise</label>
                <span class="text-[10px] text-slate-500">Nécessaire pour les nouveaux espaces</span>
            </div>
            <input id="tenant" type="text" name="tenant" value="{{ old('tenant', request('tenant')) }}" autocomplete="organization"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="ex. ma-boutique">
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-3">
                <label for="password" class="block text-xs font-medium text-slate-300">Mot de passe</label>
                <a href="{{ route('password.request') }}" class="text-xs text-amber-400 transition hover:text-amber-300">Mot de passe oublié ?</a>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="Votre mot de passe">
        </div>

        <label class="flex items-center gap-2 text-xs text-slate-400">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500">
            Rester connecté
        </label>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-slate-900">
            Se connecter
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </button>
    </form>
@endsection
