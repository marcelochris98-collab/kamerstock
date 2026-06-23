@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium">Sauvegardes / Détails</p>
            <h1 class="text-xl font-bold text-slate-900 mt-1">Sauvegarde #{{ $backup->id }}</h1>
        </div>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <a href="{{ route('landlord.backups.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 rounded-xl transition select-none">
                Retour à la liste
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-250 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-250 text-rose-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Security Alert --}}
    <div class="mb-6 p-4 bg-slate-900 border border-slate-800 text-slate-400 rounded-xl text-xs flex items-center gap-2 shadow-sm select-none">
        <svg class="w-4 h-4 text-indigo-505 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>Les sauvegardes contiennent des données sensibles. Leur téléchargement doit être limité aux administrateurs autorisés.</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Informations Générales</h3>

                <ul class="space-y-4 text-xs">
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Boutique</span>
                        <a href="{{ route('landlord.tenants.show', $backup->tenant) }}" class="font-bold text-indigo-650 hover:underline">
                            {{ $backup->tenant?->name }}
                        </a>
                    </li>
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Nom de fichier</span>
                        <span class="font-mono text-slate-800 select-all font-semibold">{{ $backup->filename }}</span>
                    </li>
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Chemin relatif</span>
                        <span class="font-mono text-slate-800 select-all">{{ $backup->path }}</span>
                    </li>
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Disque de stockage</span>
                        <span class="font-semibold text-slate-800 capitalize">{{ $backup->disk }}</span>
                    </li>
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Base de données cible</span>
                        <span class="font-mono text-slate-800 select-all font-semibold">{{ $backup->database_name }}</span>
                    </li>
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Checksum (MD5)</span>
                        <span class="font-mono text-slate-700 select-all">{{ $backup->checksum ?: 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center pb-2 border-b border-slate-100">
                        <span class="text-slate-400">Type de sauvegarde</span>
                        <span class="font-semibold text-slate-850">{{ $backup->backupTypeLabel() }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Statut</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $backup->statusBadgeClass() }}">
                            {{ $backup->statusLabel() }}
                        </span>
                    </li>
                </ul>
            </div>

            @if($backup->isFailed() && $backup->error_message)
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-rose-800 mb-3">Détails de l'erreur</h3>
                    <div class="bg-white border border-rose-100 rounded-xl p-4 font-mono text-[10px] text-rose-700 break-words leading-relaxed select-all">
                        {{ $backup->error_message }}
                    </div>
                </div>
            @endif

            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Métadonnées</h3>
                @if(!empty($metadata))
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 font-mono text-[10px] text-slate-650 leading-relaxed select-all">
                        <pre>{{ json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @else
                    <p class="text-xs text-slate-400">Aucune métadonnée supplémentaire.</p>
                @endif
            </div>
        </div>

        {{-- Actions Card --}}
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Temps et Actions</h3>

                <ul class="space-y-3.5 text-xs mb-6">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Taille</span>
                        <span class="font-bold text-slate-850">{{ $backup->sizeForHumans() }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Démarrée le</span>
                        <span class="font-semibold text-slate-800">{{ $backup->started_at ? $backup->started_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Terminée le</span>
                        <span class="font-semibold text-slate-800">{{ $backup->finished_at ? $backup->finished_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Durée d'exécution</span>
                        <span class="font-semibold text-slate-800">
                            @if($backup->started_at && $backup->finished_at)
                                {{ $backup->started_at->diffInSeconds($backup->finished_at) }} seconde(s)
                            @else
                                N/A
                            @endif
                        </span>
                    </li>
                </ul>

                <div class="space-y-3">
                    @if($backup->isCompleted() && config('platform.backups.allow_download', false))
                        <a href="{{ route('landlord.backups.download', $backup) }}" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-center transition text-xs shadow-md shadow-emerald-600/10 select-none block">
                            Télécharger le fichier SQL
                        </a>
                    @endif

                    @if($backup->isFailed() || $backup->isPending())
                        <form action="{{ route('landlord.backups.run', $backup) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition text-xs shadow-md shadow-indigo-600/10 select-none">
                                Relancer la sauvegarde
                            </button>
                        </form>
                    @endif

                    {{-- Restore Button (Disabled by default) --}}
                    <button type="button" disabled class="w-full py-2.5 bg-slate-100 text-slate-400 font-bold rounded-xl text-xs border border-slate-200 cursor-not-allowed select-none" title="Restauration non disponible">
                        Restaurer (indisponible)
                    </button>
                    <p class="text-[10px] text-slate-400 leading-tight text-center">
                        La restauration automatique n'est pas encore activée. La restauration sera ajoutée après validation des sauvegardes.
                    </p>

                    <hr class="border-slate-100 my-4" />

                    <form action="{{ route('landlord.backups.destroy', $backup) }}" method="POST" onsubmit="return confirm('Confirmer la suppression définitive de cette sauvegarde et de son fichier ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold rounded-xl transition text-xs select-none">
                            Supprimer la sauvegarde
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
