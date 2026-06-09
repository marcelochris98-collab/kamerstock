@extends('layouts.app')

@section('title', 'Crédit intelligent')
@section('page-title', 'Crédit intelligent')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg">
    @foreach($errors->all() as $error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h1 class="text-sm font-semibold text-slate-800">Configuration du crédit intelligent</h1>
        <p class="text-xs text-slate-400 mt-1">
            Ces règles permettent à chaque entreprise de définir ses propres critères d'éligibilité au crédit.
        </p>
    </div>

    <form method="POST" action="{{ route('settings.credit.update') }}" class="p-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nombre minimum d’achats</label>
                <input type="number" name="min_sales" value="{{ old('min_sales', $settings->min_sales) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Ancienneté minimum en mois</label>
                <input type="number" name="min_months" value="{{ old('min_months', $settings->min_months) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Score minimum</label>
                <input type="number" name="min_score" value="{{ old('min_score', $settings->min_score) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Coefficient Régulier</label>
                <input type="number" step="0.01" name="regular_coefficient" value="{{ old('regular_coefficient', $settings->regular_coefficient) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Coefficient Fidèle</label>
                <input type="number" step="0.01" name="loyal_coefficient" value="{{ old('loyal_coefficient', $settings->loyal_coefficient) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Coefficient Premium</label>
                <input type="number" step="0.01" name="premium_coefficient" value="{{ old('premium_coefficient', $settings->premium_coefficient) }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-medium text-slate-600 mb-1">Plafond maximum global</label>
            <input type="number" name="max_credit_limit" value="{{ old('max_credit_limit', $settings->max_credit_limit) }}"
                class="w-full md:w-1/3 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="allow_regular" value="1" {{ $settings->allow_regular ? 'checked' : '' }}>
                Autoriser les clients Réguliers
            </label>

            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="allow_loyal" value="1" {{ $settings->allow_loyal ? 'checked' : '' }}>
                Autoriser les clients Fidèles
            </label>

            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="allow_premium" value="1" {{ $settings->allow_premium ? 'checked' : '' }}>
                Autoriser les clients Premium
            </label>

            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="allow_high_risk" value="1" {{ $settings->allow_high_risk ? 'checked' : '' }}>
                Autoriser les clients à risque élevé
            </label>

            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" name="allow_admin_exception" value="1" {{ $settings->allow_admin_exception ? 'checked' : '' }}>
                Autoriser les exceptions admin
            </label>
        </div>

        <button type="submit"
            class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Enregistrer les paramètres
        </button>
    </form>
</div>

@endsection
