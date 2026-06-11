@extends('layouts.client-portal')

@section('title', 'Connexion')

@section('content')

<div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm p-6 mt-8">
    <div class="text-center mb-6">
        <h1 class="text-lg font-bold text-slate-800">Espace client</h1>
        <p class="text-xs text-slate-400 mt-1">Connectez-vous avec votre téléphone et votre code PIN.</p>
    </div>

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
        @foreach($errors->all() as $error)
            <p class="text-xs text-red-500">{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('client.portal.authenticate') }}">
        @csrf

        <div class="mb-4">
            <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                placeholder="Ex: 699000000"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>

        <div class="mb-5">
            <label class="block text-xs font-medium text-slate-600 mb-1">Code PIN</label>
            <input type="password" name="pin"
                placeholder="Code à 4 chiffres"
                class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>

        <button class="w-full px-4 py-3 bg-slate-900 text-white rounded-xl text-sm font-semibold">
            Se connecter
        </button>
    </form>
</div>

@endsection