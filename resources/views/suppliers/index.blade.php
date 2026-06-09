@extends('layouts.app')

@section('title', 'Fournisseurs')
@section('page-title', 'Fournisseurs')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->has('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
    {{ $errors->first('error') }}
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Liste des fournisseurs</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $suppliers->total() }} fournisseur(s)</p>
    </div>
   <!-- @if(auth()->user()->hasPermission('suppliers.manage'))
    <button onclick="openCreate()"
        class="flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Ajouter
    </button>
    @endif-->
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Nom</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Contact</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Telephone</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Email</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Produits</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $supplier->name }}</p>
                    @if($supplier->address)
                    <p class="text-xs text-slate-400">{{ $supplier->address }}</p>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $supplier->contact_person ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $supplier->phone ?? '—' }}</td>
                <td class="px-5 py-3 text-xs text-slate-600">{{ $supplier->email ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs">
                        {{ $supplier->products_count }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-1.5">
                        @if(auth()->user()->hasPermission('suppliers.manage'))
                        <button onclick="openEdit({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', '{{ $supplier->phone }}', '{{ $supplier->email }}', '{{ addslashes($supplier->address) }}', '{{ addslashes($supplier->contact_person) }}')"
                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-500 hover:border-amber-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                            onsubmit="return confirm('Desactiver ce fournisseur ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun fournisseur</p>
                    <p class="text-xs text-slate-300 mt-1">Ajoutez votre premier fournisseur</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($suppliers->hasPages())
    <div class="px-5 py-3 border-t border-slate-50 flex items-center justify-between">
        <p class="text-xs text-slate-400">{{ $suppliers->firstItem() }} - {{ $suppliers->lastItem() }} sur {{ $suppliers->total() }}</p>
        <div class="flex gap-2">
            @if($suppliers->onFirstPage())
            <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Precedent</span>
            @else
            <a href="{{ $suppliers->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Precedent</a>
            @endif
            @if($suppliers->hasMorePages())
            <a href="{{ $suppliers->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Suivant</a>
            @else
            <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Suivant</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Modal creer --}}
<div id="createModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Nouveau fournisseur</h3>
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" placeholder="Nom du fournisseur"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Telephone</label>
                    <input type="text" name="phone" placeholder="+237 6XX XXX XXX"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" placeholder="email@fournisseur.cm"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Personne contact</label>
                    <input type="text" name="contact_person" placeholder="Nom du contact"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                    <input type="text" name="address" placeholder="Douala, Cameroun"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="button" onclick="closeCreate()"
                    class="flex-1 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal modifier --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Modifier le fournisseur</h3>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3 mb-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom</label>
                    <input type="text" name="name" id="editName"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Telephone</label>
                    <input type="text" name="phone" id="editPhone"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" id="editEmail"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Personne contact</label>
                    <input type="text" name="contact_person" id="editContact"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Adresse</label>
                    <input type="text" name="address" id="editAddress"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="button" onclick="closeEdit()"
                    class="flex-1 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                    Sauvegarder
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openCreate() {
    document.getElementById('createModal').classList.remove('hidden');
    document.getElementById('createModal').classList.add('flex');
}
function closeCreate() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createModal').classList.remove('flex');
}
function openEdit(id, name, phone, email, address, contact) {
    document.getElementById('editName').value    = name;
    document.getElementById('editPhone').value   = phone;
    document.getElementById('editEmail').value   = email;
    document.getElementById('editAddress').value = address;
    document.getElementById('editContact').value = contact;
    document.getElementById('editForm').action   = `/suppliers/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
@endpush
