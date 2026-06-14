@extends('layouts.app')

@section('title', 'Gestion des Sauvegardes')
@section('page-title', 'Base de données / Sauvegardes')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->has('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600 font-semibold">
    {{ $errors->first('error') }}
</div>
@endif

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-sm font-semibold text-slate-800">Sauvegardes de la base de données</h1>
            <p class="text-xs text-slate-400 mt-0.5">Générer des sauvegardes régulières et restaurer en cas de besoin.</p>
        </div>

        <form method="POST" action="{{ route('admin.backups.create') }}">
            @csrf
            <button type="submit"
                class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                Générer une Sauvegarde
            </button>
        </form>
    </div>

    {{-- Avertissement de Restauration --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-700 font-bold">
            !
        </div>
        <div class="text-xs text-amber-800">
            <span class="font-bold block mb-1">Attention aux restaurations</span>
            La restauration d'une ancienne sauvegarde écrasera l'intégralité des données actuelles du système (ventes, stocks, crédits).
            Il est fortement recommandé de générer une sauvegarde de sécurité immédiate avant d'appliquer une ancienne version.
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Nom du fichier</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date de création</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Taille</th>
                    <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($backups as $b)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                    <td class="px-5 py-3 text-xs font-semibold text-slate-800 font-mono">
                        {{ $b['filename'] }}
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-500">
                        {{ $b['created_at'] }}
                    </td>
                    <td class="px-5 py-3 text-xs text-right text-slate-600 font-medium">
                        {{ $b['size'] }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.backups.download', $b['filename']) }}"
                                class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                                Télécharger
                            </a>

                            <form method="POST" action="{{ route('admin.backups.restore', $b['filename']) }}"
                                onsubmit="return confirm('Êtes-vous ABSOLUMENT sûr de vouloir restaurer cette sauvegarde ? TOUTES les données actuelles seront remplacées.')">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-1.5 border border-amber-200 rounded-lg text-xs text-amber-600 hover:bg-amber-50 transition font-semibold">
                                    Restaurer
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.backups.destroy', $b['filename']) }}"
                                onsubmit="return confirm('Supprimer ce fichier de sauvegarde ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 border border-red-200 rounded-lg text-xs text-red-500 hover:bg-red-50 transition">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center">
                        <p class="text-sm font-medium text-slate-400">Aucun fichier de sauvegarde disponible</p>
                        <p class="text-xs text-slate-300 mt-1">Cliquez sur « Générer une Sauvegarde » pour en créer une.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
