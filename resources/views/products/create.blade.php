@extends('layouts.app')

@section('title', 'Nouveau produit')
@section('page-title', 'Nouveau produit')

@section('content')

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('products.index') }}"
            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-slate-600 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-sm font-semibold text-slate-800">Ajouter un produit</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('products.store') }}">
            @csrf

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Nom du produit <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Ex: Ciment Portland"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Reference <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="reference" value="{{ old('reference') }}"
                        placeholder="Ex: CIM-001"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('reference') border-red-300 @enderror">
                    @error('reference') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Categorie</label>
                    <select name="category_id"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="">-- Aucune --</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fournisseur</label>
                    <select name="supplier_id"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="">-- Aucun --</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Unite <span class="text-red-400">*</span>
                    </label>
                    <select name="unit"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="piece" {{ old('unit') == 'piece' ? 'selected' : '' }}>Piece</option>
                        <option value="metre" {{ old('unit') == 'metre' ? 'selected' : '' }}>Metre</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogramme</option>
                        <option value="litre" {{ old('unit') == 'litre' ? 'selected' : '' }}>Litre</option>
                        <option value="boite" {{ old('unit') == 'boite' ? 'selected' : '' }}>Boite</option>
                        <option value="sachet" {{ old('unit') == 'sachet' ? 'selected' : '' }}>Sachet</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Prix achat (FCFA) <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="price_buy" value="{{ old('price_buy', 0) }}"
                        min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('price_buy') border-red-300 @enderror">
                    @error('price_buy') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Prix vente (FCFA) <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="price_sell" value="{{ old('price_sell', 0) }}"
                        min="0" step="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('price_sell') border-red-300 @enderror">
                    @error('price_sell') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Quantite initiale <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="quantity" value="{{ old('quantity', 0) }}"
                        min="0"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('quantity') border-red-300 @enderror">
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">
                        Seuil alerte stock <span class="text-red-400">*</span>
                    </label>
                    <input type="number" name="alert_threshold" value="{{ old('alert_threshold', 5) }}"
                        min="0"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('alert_threshold') border-red-300 @enderror">
                    @error('alert_threshold') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                <textarea name="description" rows="3"
                    placeholder="Description optionnelle du produit..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Apercu marge --}}
            <div class="bg-slate-50 rounded-lg p-3 mb-6" id="margePreview">
                <p class="text-xs text-slate-500">Marge : <span id="margeValeur" class="font-semibold text-slate-700">0 FCFA</span>
                <span id="margePct" class="text-slate-400 ml-2"></span></p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-5 py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Enregistrer le produit
                </button>
                <a href="{{ route('products.index') }}"
                    class="px-5 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const priceBuy  = document.querySelector('[name="price_buy"]');
const priceSell = document.querySelector('[name="price_sell"]');

function updateMarge() {
    const buy  = parseFloat(priceBuy.value) || 0;
    const sell = parseFloat(priceSell.value) || 0;
    const marge = sell - buy;
    const pct   = buy > 0 ? ((marge / buy) * 100).toFixed(1) : 0;
    document.getElementById('margeValeur').textContent = marge.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('margeValeur').className = marge >= 0
        ? 'font-semibold text-emerald-600'
        : 'font-semibold text-red-500';
    document.getElementById('margePct').textContent = buy > 0 ? `(${pct}%)` : '';
}

priceBuy.addEventListener('input', updateMarge);
priceSell.addEventListener('input', updateMarge);
updateMarge();
</script>
@endpush
