@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-900">Sauvegardes de Bases de Données</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Consultez l'historique global des sauvegardes de données générées par les boutiques clientes</p>
    </div>

    {{-- Content Table --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 bg-slate-50/50 uppercase tracking-wider">
                        <th class="px-6 py-4">Boutique</th>
                        <th class="px-6 py-4">Nom de fichier</th>
                        <th class="px-6 py-4">Chemin / Disque</th>
                        <th class="px-6 py-4">Taille</th>
                        <th class="px-6 py-4">Créée le</th>
                        <th class="px-6 py-4 text-right">Statut</th>
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
                            <td class="px-6 py-4 font-mono text-[10px] text-slate-700 select-all">{{ $backup->filename }}</td>
                            <td class="px-6 py-4">
                                <p class="text-slate-650 leading-none">{{ $backup->path ?? '/' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1 leading-none">Disque: {{ $backup->disk ?? 'default' }}</p>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ number_format($backup->size_bytes / 1024 / 1024, 2) }} Mo</td>
                            <td class="px-6 py-4 text-slate-500">{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                    {{ $backup->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-450">Aucun enregistrement de sauvegarde trouvé.</td>
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
