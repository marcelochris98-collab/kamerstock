@extends('layouts.app')

@section('title', 'Fournisseurs')
@section('page-title', 'Fournisseurs')

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
        <h1 class="text-sm font-semibold text-slate-800">Fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $suppliers->total() }} fournisseur(s) actif(s)</p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('export', 'suppliers') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Exporter CSV
        </a>
        @if(auth()->user()->hasPermission('suppliers.manage'))
        <button onclick="document.getElementById('supplier-form').classList.toggle('hidden')"
            class="px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            Ajouter fournisseur
        </button>
        @endif
    </div>
</div>

@if(auth()->user()->hasPermission('suppliers.manage'))
<div id="supplier-form" class="hidden bg-white rounded-xl shadow-sm p-5 mb-5">
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Nom</label>
                <input type="text" name="name" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Téléphone</label>
                <input type="text" name="phone"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                <input type="email" name="email"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                <input type="text" name="address"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Personne contact</label>
                <input type="text" name="contact_person"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Notes</label>
                <input type="text" name="notes"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
        </div>

        <button type="submit"
            class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-700 transition">
            Enregistrer
        </button>
    </form>
</div>
@endif

{{-- Filtres --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, téléphone, contact..."
            class="px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 w-72 bg-slate-50 focus:bg-white transition">
        <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Filtrer
        </button>
        @if(request('search'))
        <a href="{{ route('suppliers.index') }}" class="text-xs text-slate-400 hover:text-slate-600 transition pl-1">
            Réinitialiser
        </a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Contact</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Produits</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse($suppliers as $supplier)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $supplier->name }}</p>
                    <p class="text-xs text-slate-400">{{ $supplier->address ?? 'Adresse non renseignée' }}</p>
                </td>

                <td class="px-5 py-3">
                    <p class="text-xs text-slate-600">{{ $supplier->phone ?? '—' }}</p>
                    <p class="text-xs text-slate-400">{{ $supplier->email ?? '—' }}</p>
                </td>

                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        {{ $supplier->products_count }}
                    </span>
                </td>

                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('suppliers.show', $supplier) }}"
                            class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-50 transition">
                            Voir
                        </a>

                        @if(auth()->user()->hasPermission('suppliers.manage'))
                        <button onclick="document.getElementById('edit-supplier-{{ $supplier->id }}').classList.toggle('hidden')"
                            class="px-3 py-1.5 border border-amber-200 rounded-lg text-xs text-amber-600 hover:bg-amber-50 transition">
                            Modifier
                        </button>

                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                            onsubmit="return confirm('Supprimer ou désactiver ce fournisseur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-3 py-1.5 border border-red-200 rounded-lg text-xs text-red-500 hover:bg-red-50 transition">
                                Supprimer
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>

            @if(auth()->user()->hasPermission('suppliers.manage'))
            <tr id="edit-supplier-{{ $supplier->id }}" class="hidden bg-slate-50 border-b border-slate-100">
                <td colspan="4" class="px-5 py-4">
                    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            <input type="text" name="name" value="{{ $supplier->name }}" required
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="text" name="phone" value="{{ $supplier->phone }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="email" name="email" value="{{ $supplier->email }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="text" name="address" value="{{ $supplier->address }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="text" name="contact_person" value="{{ $supplier->contact_person }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">

                            <input type="text" name="notes" value="{{ $supplier->notes }}"
                                class="px-3 py-2 border border-slate-200 rounded-lg text-xs">
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
                <td colspan="4" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun fournisseur trouvé</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($suppliers->hasPages())
    <div class="px-5 py-3 border-t border-slate-50">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>

@endsection