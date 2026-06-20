@extends('layouts.app')

@section('title', 'Produits')
@section('page-title', 'Produits')

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

{{-- Header page --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div>
        <h1 class="text-sm font-semibold text-slate-800">Liste des produits</h1>
        <p class="text-xs text-slate-400 mt-0.5">{{ $products->total() }} produit(s) actif(s)</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('export', 'products') }}"
            class="px-4 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
            Exporter CSV
        </a>
        @if(auth()->user()->hasPermission('products.create'))
        <a href="{{ route('products.create') }}"
            class="flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter un produit
        </a>
        @endif
    </div>
</div>

{{-- Filtres --}}
<div class="mb-4">
    <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <select name="category_id" onchange="this.form.submit()"
            class="w-full sm:w-auto px-3 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-slate-400 bg-white">
            <option value="">Toutes les categories</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
            @endforeach
        </select>
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Rechercher un produit..."
            class="w-full sm:w-56 px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 sm:flex-none px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium rounded-lg transition">
                Rechercher
            </button>
            @if(request('search') || request('category_id'))
            <a href="{{ route('products.index') }}"
                class="px-3 py-2 text-xs text-slate-400 hover:text-slate-600 transition flex items-center justify-center">
                Reinitialiser
            </a>
            @endif
        </div>
    </form>
</div>

{{-- Tableau --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full min-w-[800px]">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Categorie</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Fournisseur</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Unite</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix achat</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Prix vente</th>
                <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Stock</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                <td class="px-5 py-3">
                    <p class="text-xs font-semibold text-slate-800">{{ $product->name }}</p>
                    <p class="text-xs text-slate-400 font-mono">{{ $product->reference }}</p>
                </td>
                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $product->category?->name ?? '—' }}
                </td>
                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $product->supplier?->name ?? '—' }}
                </td>
                <td class="px-5 py-3 text-xs text-slate-600">
                    {{ $product->unit_label }}
                </td>
                <td class="px-5 py-3 text-xs text-right text-slate-600">
                    {{ number_format($product->price_buy, 0, ',', ' ') }} F
                </td>
                <td class="px-5 py-3 text-xs text-right font-semibold text-slate-800">
                    {{ number_format($product->price_sell, 0, ',', ' ') }} F
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $product->isLowStock() ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $product->quantity }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center justify-end gap-1.5">
                        <a href="{{ route('products.show', $product) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-500 hover:border-blue-200 transition"
                            title="Voir">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        @if(auth()->user()->hasPermission('products.edit'))
                        <a href="{{ route('products.edit', $product) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-500 hover:border-amber-200 transition"
                            title="Modifier">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        @endif
                        @if(auth()->user()->hasPermission('products.delete'))
                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                            onsubmit="return confirm('Desactiver ce produit ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 transition"
                                title="Supprimer">
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
                <td colspan="8" class="px-5 py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Aucun produit trouve</p>
                    <p class="text-xs text-slate-300 mt-1">Ajoutez votre premier produit</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="px-5 py-3 border-t border-slate-50 flex items-center justify-between">
        <p class="text-xs text-slate-400">
            {{ $products->firstItem() }} - {{ $products->lastItem() }} sur {{ $products->total() }}
        </p>
        <div class="flex items-center gap-2">
            @if($products->onFirstPage())
            <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Precedent</span>
            @else
            <a href="{{ $products->previousPageUrl() }}"
                class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                Precedent
            </a>
            @endif
            @if($products->hasMorePages())
            <a href="{{ $products->nextPageUrl() }}"
                class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">
                Suivant
            </a>
            @else
            <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Suivant</span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
