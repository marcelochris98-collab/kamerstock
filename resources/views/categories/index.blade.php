@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

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

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Nouvelle categorie</h2>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Ex: Plomberie"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                    class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Ajouter
                </button>
            </form>
        </div>
    </div>

    <div class="col-span-2">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                <p class="text-sm font-semibold text-slate-800">Toutes les categories</p>
                <span class="text-xs text-slate-400">{{ $categories->total() }} categorie(s)</span>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Nom</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Produits</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                        <td class="px-5 py-3 text-xs font-medium text-slate-700">{{ $category->name }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs">
                                {{ $category->products_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button onclick="openEdit({{ $category->id }}, '{{ $category->name }}')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-500 hover:border-amber-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                    onsubmit="return confirm('Supprimer cette categorie ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-12 text-center text-xs text-slate-400">Aucune categorie</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($categories->hasPages())
            <div class="px-5 py-3 border-t border-slate-50 flex items-center justify-between">
                <p class="text-xs text-slate-400">{{ $categories->firstItem() }} - {{ $categories->lastItem() }} sur {{ $categories->total() }}</p>
                <div class="flex gap-2">
                    @if($categories->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Precedent</span>
                    @else
                    <a href="{{ $categories->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Precedent</a>
                    @endif
                    @if($categories->hasMorePages())
                    <a href="{{ $categories->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Suivant</a>
                    @else
                    <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Suivant</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Modifier la categorie</h3>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-medium text-slate-600 mb-1">Nom</label>
                <input type="text" name="name" id="editName"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
            <div class="flex gap-2">
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
function openEdit(id, name) {
    document.getElementById('editName').value = name;
    document.getElementById('editForm').action = `/categories/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
@endpush
