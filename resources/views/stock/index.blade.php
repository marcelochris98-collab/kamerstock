@extends('layouts.app')

@section('title', 'Stock')
@section('page-title', 'Mouvements de stock')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
    @foreach($errors->all() as $error)
    <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="grid grid-cols-3 gap-6">

    {{-- Formulaire mouvement --}}
    @if(auth()->user()->hasPermission('stock.manage'))
    <div class="col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-5" id="nouveau">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Nouveau mouvement</h2>
            <form method="POST" action="{{ route('stock.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Produit <span class="text-red-400">*</span></label>
                    <select name="product_id"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('product_id') border-red-300 @enderror">
                        <option value="">-- Choisir --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->quantity }} en stock)
                        </option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Type <span class="text-red-400">*</span></label>
                    <select name="type"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="entree" {{ old('type') == 'entree' ? 'selected' : '' }}>Entree</option>
                        <option value="sortie" {{ old('type') == 'sortie' ? 'selected' : '' }}>Sortie</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Quantite <span class="text-red-400">*</span></label>
                    <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                        min="1" max="10000"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('quantity') border-red-300 @enderror">
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Motif</label>
                    <input type="text" name="reason" value="{{ old('reason') }}"
                        placeholder="Ex: Livraison fournisseur"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <button type="submit"
                    class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Enregistrer
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Liste mouvements --}}
    <div class="{{ auth()->user()->hasPermission('stock.manage') ? 'col-span-2' : 'col-span-3' }}">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                <p class="text-sm font-semibold text-slate-800">Historique des mouvements</p>
                <span class="text-xs text-slate-400">{{ $movements->total() }} mouvement(s)</span>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Type</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Quantite</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Motif</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Par</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $mvt)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                        <td class="px-5 py-3 text-xs text-slate-400">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3">
                            <p class="text-xs font-medium text-slate-700">{{ $mvt->product->name }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ $mvt->product->reference }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                {{ $mvt->type === 'entree' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                {{ $mvt->type === 'entree' ? 'Entree' : 'Sortie' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center text-xs font-semibold text-slate-700">
                            {{ $mvt->type === 'entree' ? '+' : '-' }}{{ $mvt->quantity }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $mvt->reason ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $mvt->user->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-xs text-slate-400">Aucun mouvement enregistre</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($movements->hasPages())
            <div class="px-5 py-3 border-t border-slate-50 flex items-center justify-between">
                <p class="text-xs text-slate-400">{{ $movements->firstItem() }} - {{ $movements->lastItem() }} sur {{ $movements->total() }}</p>
                <div class="flex gap-2">
                    @if($movements->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Precedent</span>
                    @else
                    <a href="{{ $movements->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Precedent</a>
                    @endif
                    @if($movements->hasMorePages())
                    <a href="{{ $movements->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition">Suivant</a>
                    @else
                    <span class="px-3 py-1.5 text-xs text-slate-300 border border-slate-100 rounded-lg">Suivant</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
