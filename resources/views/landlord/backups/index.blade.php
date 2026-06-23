@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Sauvegardes de Bases de Données</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Consultez et gérez l'historique global des sauvegardes de données générées par les boutiques clientes</p>
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

    {{-- Quick Backup Action --}}
    @if(config('platform.backups.allow_manual_backup', true))
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 mb-6">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lancer une sauvegarde manuelle</h2>
            <form id="newBackupForm" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                @csrf
                <div class="flex-1 w-full">
                    <label for="tenant_select" class="block text-xs font-semibold text-slate-500 mb-1.5">Sélectionner une boutique</label>
                    <select id="tenant_select" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs text-slate-850 focus:outline-none focus:border-indigo-500" required>
                        <option value="">-- Choisir une boutique éligible --</option>
                        @foreach($tenants as $tenant)
                            @if($tenant->provisioning_status !== 'prepared')
                                <option value="{{ $tenant->slug }}">{{ $tenant->name }} ({{ $tenant->slug }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <button type="submit" onclick="event.preventDefault(); triggerManualBackup();" class="px-5 py-2.5 bg-indigo-650 hover:bg-indigo-755 text-xs font-bold text-white rounded-xl shadow-md shadow-indigo-650/10 transition select-none flex items-center justify-center gap-1.5 whitespace-nowrap w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Démarrer la sauvegarde
                </button>
            </form>
            <script>
                function triggerManualBackup() {
                    const select = document.getElementById('tenant_select');
                    const slug = select.value;
                    if (!slug) {
                        alert('Veuillez sélectionner une boutique.');
                        return;
                    }
                    const form = document.getElementById('newBackupForm');
                    form.action = `/landlord/tenants/${slug}/backups`;
                    form.submit();
                }
            </script>
        </div>
    @endif

    {{-- Filters Bar --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 mb-6">
        <form method="GET" action="{{ route('landlord.backups.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label for="filter_tenant" class="block text-xs font-semibold text-slate-500 mb-1.5">Boutique</label>
                <select id="filter_tenant" name="tenant_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs text-slate-850 focus:outline-none">
                    <option value="">Toutes les boutiques</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter_type" class="block text-xs font-semibold text-slate-500 mb-1.5">Type</label>
                <select id="filter_type" name="backup_type" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs text-slate-850 focus:outline-none">
                    <option value="">Tous les types</option>
                    <option value="manual" {{ request('backup_type') === 'manual' ? 'selected' : '' }}>Manuelle</option>
                    <option value="automatic" {{ request('backup_type') === 'automatic' ? 'selected' : '' }}>Automatique</option>
                    <option value="pre_migration" {{ request('backup_type') === 'pre_migration' ? 'selected' : '' }}>Pré-migration</option>
                </select>
            </div>
            <div>
                <label for="filter_status" class="block text-xs font-semibold text-slate-500 mb-1.5">Statut</label>
                <select id="filter_status" name="status" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs text-slate-850 focus:outline-none">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminée</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échouée</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-900 hover:bg-slate-800 text-xs font-bold text-white rounded-xl transition">
                    Filtrer
                </button>
                <a href="{{ route('landlord.backups.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 rounded-xl transition text-center flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Nom de fichier</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Taille</th>
                        <th class="px-6 py-4">Créée le</th>
                        <th class="px-6 py-4">Statut</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($backups as $backup)
                        <tr class="hover:bg-slate-50/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('landlord.tenants.show', $backup->tenant) }}" class="font-bold text-slate-900 hover:text-indigo-650">
                                    {{ $backup->tenant?->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $backup->tenant?->slug }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-[10px] text-slate-700 select-all">
                                <a href="{{ route('landlord.backups.show', $backup) }}" class="hover:underline text-indigo-650 font-semibold">
                                    {{ $backup->filename }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-650">{{ $backup->backupTypeLabel() }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $backup->sizeForHumans() }}</td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $backup->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $backup->statusBadgeClass() }}">
                                    {{ $backup->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('landlord.backups.show', $backup) }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-[10px] font-bold text-slate-700 rounded-lg transition" title="Détails">
                                    Détails
                                </a>
                                @if($backup->isCompleted() && config('platform.backups.allow_download', false))
                                    <a href="{{ route('landlord.backups.download', $backup) }}" class="inline-flex items-center px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold rounded-lg transition text-[10px]" title="Télécharger">
                                        Télécharger
                                    </a>
                                @endif
                                <form action="{{ route('landlord.backups.destroy', $backup) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmer la suppression de cette sauvegarde ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 rounded-lg transition text-[10px]" title="Supprimer">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-450">Aucun enregistrement de sauvegarde trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($backups->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $backups->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
