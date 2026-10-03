@extends('layouts.app')

@section('title', 'Mon Profil & Sécurité')
@section('page-title', 'Mon Profil & Sécurité')

@section('content')

@if(session('status') === 'profile-updated')
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    Vos informations de profil ont été mises à jour avec succès.
</div>
@endif

@if(session('status') === 'password-updated')
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    Votre mot de passe a été modifié avec succès.
</div>
@endif

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div class="max-w-4xl space-y-6">

    {{-- Carte 1: Informations Personnelles --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3 mb-5 border-b border-slate-50 pb-4">
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Informations Personnelles</h3>
                <p class="text-xs text-slate-400">Modifiez votre nom complet et votre adresse e-mail d'accès à la boutique.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4 max-w-lg">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-xs font-medium text-slate-700 mb-1">Nom complet</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('name') border-red-300 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-700 mb-1">Adresse Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('email') border-red-300 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    Mettre à jour le profil
                </button>
            </div>
        </form>
    </div>

    {{-- Carte 2: Modification du Mot de Passe --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3 mb-5 border-b border-slate-50 pb-4">
            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Changer de Mot de Passe</h3>
                <p class="text-xs text-slate-400">Sécurisez votre accès en mettant à jour votre mot de passe.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4 max-w-lg">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="block text-xs font-medium text-slate-700 mb-1">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('current_password', 'updatePassword') border-red-300 @enderror">
                @error('current_password', 'updatePassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-700 mb-1">Nouveau mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="new-password"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('password', 'updatePassword') border-red-300 @enderror">
                @error('password', 'updatePassword')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-slate-700 mb-1">Confirmer le nouveau mot de passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="pt-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                    Enregistrer le nouveau mot de passe
                </button>
            </div>
        </form>
    </div>

    {{-- Carte 3: Préférences de Notifications --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3 mb-5 border-b border-slate-50 pb-4">
            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-800">Préférences de Notifications</h3>
                <p class="text-xs text-slate-400">Gérez vos alertes visuelles, sonores et catégories de messages.</p>
            </div>
        </div>

        @include('profile.partials.update-notification-settings-form')
    </div>

</div>

@endsection

