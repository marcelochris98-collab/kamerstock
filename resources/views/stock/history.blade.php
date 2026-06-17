@extends('layouts.app')

@section('title', 'Historique Stock')
@section('page-title', 'Gestion de stock')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700 animate-fade-in">
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

<div x-data="{ 
    showMovementModal: {{ ($errors->has('product_id') || $errors->has('quantity') || $errors->has('movement_category') || $errors->has('reason') || old('action') === 'movement') ? 'true' : 'false' }},
    showInventoryModal: {{ ($errors->has('inventory_product_id') || $errors->has('counted_quantity') || $errors->has('inventory_reason') || old('action') === 'inventory') ? 'true' : 'false' }}
}">

    {{-- Sub-navigation Tabs --}}
    <div class="flex items-center justify-between border-b border-slate-200 mb-6 bg-white px-4 rounded-lg shadow-sm">
        <div class="flex">
            <a href="{{ route('stock.index') }}" 
               class="py-4 px-6 text-xs font-semibold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition">
                Tableau de bord
            </a>
            <a href="{{ route('stock.history') }}" 
               class="py-4 px-6 text-xs font-semibold border-b-2 border-slate-900 text-slate-900 transition">
                Historique des mouvements
            </a>
        </div>
        
        @if(auth()->user()->hasPermission('stock.manage'))
        <div class="flex gap-2 py-2">
            <button @click="showMovementModal = true" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Mouvement manuel
            </button>
            <button @click="showInventoryModal = true" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-955 text-xs font-bold rounded-lg transition shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Inventaire physique
            </button>
        </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('stock.history') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium text-slate-500 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Produit, référence, motif..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                <select name="type"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <option value="">Tous</option>
                    <option value="entree" @selected(request('type') === 'entree')>Entrées</option>
                    <option value="sortie" @selected(request('type') === 'sortie')>Sorties</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Du</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Au</label>
                <div class="flex gap-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-800 transition">
                        Filtrer
                    </button>
                    @if(request()->anyFilled(['search', 'type', 'date_from', 'date_to']))
                        <a href="{{ route('stock.history') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition flex items-center justify-center" title="Réinitialiser">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Movements Table --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Historique des mouvements</h2>
                <p class="text-xs text-slate-400 mt-0.5">Mouvements de stock triés par date décroissante</p>
            </div>
            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">{{ $movements->total() }} mouvement(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Produit</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Sens</th>
                        <th class="px-5 py-3 text-center text-xs font-medium text-slate-400">Quantité</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Motif</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Par</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($movements as $mvt)
                    <tr class="hover:bg-slate-50 transition last:border-0">
                        <td class="px-5 py-3 text-xs text-slate-400 whitespace-nowrap">{{ $mvt->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-3">
                            <p class="text-xs font-medium text-slate-700">{{ $mvt->product?->name ?? 'Produit supprimé' }}</p>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $mvt->product?->reference }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold tracking-wide uppercase
                                {{ $mvt->type === 'entree' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                                {{ $mvt->type === 'entree' ? 'Entrée' : 'Sortie' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-center text-xs font-bold {{ $mvt->type === 'entree' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $mvt->type === 'entree' ? '+' : '-' }}{{ $mvt->quantity }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500 min-w-[200px]">{{ $mvt->reason ?? '-' }}</td>
                        <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $mvt->user?->name ?? 'Système' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-xs text-slate-400 bg-slate-25">Aucun mouvement enregistré</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="px-5 py-4 border-t border-slate-50 bg-slate-50">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    @if(auth()->user()->hasPermission('stock.manage'))
    {{-- Modal: Manual Movement --}}
    <div x-show="showMovementModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
         x-cloak>
        
        <div @click.outside="showMovementModal = false" 
             x-show="showMovementModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
            
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Mouvement manuel</h3>
                <button @click="showMovementModal = false" class="text-slate-400 hover:text-slate-650 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('stock.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="action" value="movement">

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Produit <span class="text-rose-500">*</span></label>
                    <select name="product_id" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450 @error('product_id') border-rose-350 @enderror">
                        <option value="">-- Choisir un produit --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} ({{ $product->quantity }} en stock)
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sens <span class="text-rose-500">*</span></label>
                        <select name="type" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                            <option value="entree" {{ old('type') == 'entree' ? 'selected' : '' }}>Entrée</option>
                            <option value="sortie" {{ old('type') == 'sortie' ? 'selected' : '' }}>Sortie</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Quantité <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="10000" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type de mouvement <span class="text-rose-500">*</span></label>
                    <select name="movement_category" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                        <option value="ajustement" {{ old('movement_category') == 'ajustement' ? 'selected' : '' }}>Ajustement manuel</option>
                        <option value="reception" {{ old('movement_category') == 'reception' ? 'selected' : '' }}>Réception commande</option>
                        <option value="retour_client" {{ old('movement_category') == 'retour_client' ? 'selected' : '' }}>Retour client</option>
                        <option value="retour_fournisseur" {{ old('movement_category') == 'retour_fournisseur' ? 'selected' : '' }}>Retour fournisseur</option>
                        <option value="perte_casse" {{ old('movement_category') == 'perte_casse' ? 'selected' : '' }}>Perte / casse</option>
                        <option value="autre" {{ old('movement_category') == 'autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Motif</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" placeholder="Ex: Casse constatée au rayon"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showMovementModal = false"
                        class="flex-1 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg transition text-center">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Physical Inventory --}}
    <div x-show="showInventoryModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
         x-cloak>
        
        <div @click.outside="showInventoryModal = false" 
             x-show="showInventoryModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
            
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Inventaire physique</h3>
                <button @click="showInventoryModal = false" class="text-slate-400 hover:text-slate-650 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('stock.store') }}" class="p-5">
                @csrf
                <input type="hidden" name="action" value="inventory">

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Produit compté <span class="text-rose-500">*</span></label>
                    <select name="inventory_product_id" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                        <option value="">-- Choisir un produit --</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('inventory_product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }} (système : {{ $product->quantity }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Quantité comptée <span class="text-rose-500">*</span></label>
                    <input type="number" name="counted_quantity" value="{{ old('counted_quantity') }}" min="0" max="100000" required
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Observation</label>
                    <input type="text" name="inventory_reason" value="{{ old('inventory_reason') }}" placeholder="Ex: Inventaire mensuel"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-450">
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showInventoryModal = false"
                        class="flex-1 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg transition text-center">
                        Annuler
                    </button>
                    <button type="submit"
                        class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-lg transition">
                        Valider l'inventaire
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@endsection
