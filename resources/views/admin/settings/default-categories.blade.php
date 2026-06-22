@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">

    {{-- Header --}}
    <div class="mb-6">
        <p class="text-xs text-slate-400 font-medium">Administration / Paramètres / Initialisation</p>
        <h1 class="text-xl font-bold text-slate-900 mt-1">Catégories par défaut ({{ app(\App\Services\BusinessTypeService::class)->label() }})</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-lg text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-xl bg-white border border-slate-200 shadow-sm rounded-xl p-6">
        <h2 class="text-sm font-semibold text-slate-800 mb-2">Choisir les catégories à créer</h2>
        <p class="text-xs text-slate-500 mb-6">
            Sélectionnez les catégories recommandées pour le type d'activité <span class="font-bold text-slate-700">{{ app(\App\Services\BusinessTypeService::class)->label() }}</span>.
            Les catégories déjà présentes dans votre stock ne seront pas créées à nouveau pour éviter tout doublon.
        </p>

        <form action="{{ route('admin.settings.default-categories.store') }}" method="POST">
            @csrf

            <div class="space-y-3 mb-6">
                @forelse($categories as $category)
                    <label class="flex items-center justify-between p-3 rounded-lg border {{ $category['exists'] ? 'bg-slate-50/70 border-slate-200 text-slate-400' : 'bg-white border-slate-200 hover:border-amber-400 cursor-pointer text-slate-700 transition' }}">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="categories[]" value="{{ $category['name'] }}"
                                {{ $category['exists'] ? '' : 'checked' }}
                                class="rounded border-slate-300 text-amber-500 focus:ring-amber-500 h-4 w-4">
                            <span class="text-xs font-semibold {{ $category['exists'] ? 'line-through text-slate-450' : 'text-slate-700' }}">
                                {{ $category['name'] }}
                            </span>
                        </div>
                        @if($category['exists'])
                            <span class="px-2 py-0.5 bg-slate-200/80 text-slate-500 text-[9px] font-bold rounded">Déjà existante</span>
                        @else
                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[9px] font-bold rounded">Recommandé</span>
                        @endif
                    </label>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">Aucune catégorie par défaut recommandée pour cette activité.</p>
                @endforelse

                @error('categories')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.settings.index') }}" class="px-4 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition select-none">
                    Annuler
                </a>
                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-slate-950 text-xs font-bold rounded-lg transition shadow-sm">
                    Confirmer et créer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
