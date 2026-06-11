@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

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

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Clients</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $clients->total() }} client(s)</p>
    </div>

   <!-- @if(auth()->user()->hasPermission('clients.manage'))
    <button onclick="document.getElementById('client-form').classList.toggle('hidden')"
        class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
        Ajouter client
    </button>
    @endif-->
</div>

@if(auth()->user()->hasPermission('clients.manage'))
<div id="client-form" class="hidden bg-white rounded-xl shadow-sm p-5 mb-5">
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nom</label>
                <input type="text" name="name" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
                <input type="text" name="phone" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                <input type="email" name="email"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
                    <option value="particulier">Particulier</option>
                    <option value="entreprise">Entreprise</option>
                    <option value="revendeur">Revendeur</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                <input type="text" name="address"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs">
            </div>
        </div>

        <button type="submit"
            class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold">
            Enregistrer
        </button>
    </form>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Client</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Contact</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Score</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Crédit dispo</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Portail</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($clients as $client)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $client->name }}</p>
                    <p class="text-xs text-slate-400">{{ $client->type_label ?? $client->type ?? 'particulier' }}</p>
                </td>

                <td class="px-5 py-3">
                    <p class="text-xs text-slate-600">{{ $client->phone ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $client->email ?? '—' }}</p>
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                        {{ $client->loyalty_score ?? 0 }}/100
                    </span>
                    <p class="text-xs text-slate-400 mt-1">{{ $client->loyalty_status ?? 'occasionnel' }}</p>
                </td>

                <td class="px-5 py-3 text-center">
                    <p class="text-xs font-semibold text-emerald-600">
                        {{ number_format($client->credit_available ?? 0, 0, ',', ' ') }} FCFA
                    </p>
                </td>

                <td class="px-5 py-3 text-center">
                    @if($client->portal_enabled)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            Activé
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">
                            Désactivé
                        </span>
                    @endif
                </td>

                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2 flex-wrap">
                        <a href="{{ route('clients.show', $client) }}"
                            class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50">
                            Voir
                        </a>

                        @if(auth()->user()->hasPermission('clients.manage'))
                            @if(!$client->portal_enabled)
                                <form method="POST" action="{{ route('clients.portal.enable', $client) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs bg-emerald-50 text-emerald-600 hover:bg-emerald-100">
                                        Activer portail
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('clients.portal.disable', $client) }}"
                                    onsubmit="return confirm('Désactiver le portail de ce client ?')">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg text-xs bg-red-50 text-red-500 hover:bg-red-100">
                                        Désactiver portail
                                    </button>
                                </form>
                            @endif

                            <button onclick="document.getElementById('edit-client-{{ $client->id }}').classList.toggle('hidden')"
                                class="px-3 py-1.5 border border-amber-200 rounded-lg text-xs text-amber-600 hover:bg-amber-50">
                                Modifier
                            </button>

                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                onsubmit="return confirm('Supprimer ce client ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="px-3 py-1.5 border border-red-200 rounded-lg text-xs text-red-500 hover:bg-red-50">
                                    Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>

            @if(auth()->user()->hasPermission('clients.manage'))
            <tr id="edit-client-{{ $client->id }}" class="hidden bg-slate-50 border-b border-slate-100">
                <td colspan="6" class="px-5 py-4">
                    <form method="POST" action="{{ route('clients.update', $client) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            <input type="text" name="name" value="{{ $client->name }}" required
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="text" name="phone" value="{{ $client->phone }}" required
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="email" name="email" value="{{ $client->email }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <select name="type" class="px-3 py-2 border border-slate-200 rounded-lg text-xs">
                                <option value="particulier" {{ $client->type === 'particulier' ? 'selected' : '' }}>Particulier</option>
                                <option value="entreprise" {{ $client->type === 'entreprise' ? 'selected' : '' }}>Entreprise</option>
                                <option value="revendeur" {{ $client->type === 'revendeur' ? 'selected' : '' }}>Revendeur</option>
                            </select>

                            <input type="text" name="address" value="{{ $client->address }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs md:col-span-2">
                        </div>

                        <button type="submit"
                            class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold">
                            Mettre à jour
                        </button>
                    </form>
                </td>
            </tr>
            @endif

            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun client trouvé</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($clients->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $clients->links() }}
    </div>
    @endif
</div>

@endsection