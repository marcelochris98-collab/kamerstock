@extends('layouts.auth')

@section('title', 'Créer une entreprise')
@section('auth-heading', 'Créez votre espace')
@section('auth-description', 'Renseignez votre entreprise et créez le compte de son administrateur.')

@section('auth-content')
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="company_name" class="mb-2 block text-xs font-medium text-slate-300">Nom de l’entreprise</label>
            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required autofocus autocomplete="organization"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="Ex. Boutique du Centre">
        </div>

        <div>
            <label for="name" class="mb-2 block text-xs font-medium text-slate-300">Votre nom</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="Nom de l’administrateur">
        </div>

        <div>
            <label for="email" class="mb-2 block text-xs font-medium text-slate-300">Adresse email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="vous@entreprise.com">
        </div>

        <div>
            <label for="password" class="mb-2 block text-xs font-medium text-slate-300">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="Choisissez un mot de passe">
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-xs font-medium text-slate-300">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="w-full rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20"
                placeholder="Retapez le mot de passe">
        </div>

        <p class="text-xs leading-5 text-slate-500">Un identifiant d’entreprise unique vous sera attribué après la création.</p>

        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 focus:ring-offset-slate-900">
            Créer mon espace entreprise
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </button>
    </form>
@endsection
