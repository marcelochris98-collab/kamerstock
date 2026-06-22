@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">

    {{-- Header --}}
    <div class="mb-6">
        <p class="text-xs text-slate-400 font-medium">Administration / Paramètres</p>
        <h1 class="text-xl font-bold text-slate-900 mt-1">Configuration du commerce ({{ app(\App\Services\BusinessTypeService::class)->label() }})</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-lg text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-3xl bg-white border border-slate-200 shadow-sm rounded-xl p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Informations Générales --}}
                <div class="md:col-span-2">
                    <h2 class="text-sm font-semibold text-slate-800 mb-4">Informations Générales</h2>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom de la boutique <span class="text-red-500">*</span></label>
                    <input type="text" name="shop_name" value="{{ old('shop_name', $settings->shop_name ?? 'KamerStock') }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('shop_name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $settings->address ?? '') }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('address') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings->phone ?? '') }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('phone') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings->email ?? '') }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Zone Upload Logo avec Prévisualisation et Suppression Dynamique --}}
                <div class="md:col-span-2" x-data="logoUploader()">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Logo du commerce</label>
                    <div class="flex items-center gap-4 mt-2">
                        <!-- Preview Container -->
                        <div class="relative group">
                            <!-- Image -->
                            <template x-if="previewUrl">
                                <img :src="previewUrl" alt="Logo Preview" class="w-16 h-16 rounded-xl object-cover border border-slate-200 shadow-sm transition-all duration-300 group-hover:scale-105">
                            </template>
                            <!-- Placeholder -->
                            <template x-if="!previewUrl">
                                <div class="w-16 h-16 rounded-xl bg-slate-100 border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400 text-[10px] font-semibold transition-all duration-300">
                                    <svg class="w-5 h-5 mb-0.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Aucun</span>
                                </div>
                            </template>
                            <!-- Delete Button -->
                            <template x-if="previewUrl">
                                <button type="button" @click="removeLogo()" 
                                    class="absolute -top-1.5 -right-1.5 bg-red-500 hover:bg-red-650 text-white rounded-full p-0.5 shadow-md hover:scale-110 active:scale-90 transition-all duration-150"
                                    title="Supprimer le logo">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </template>
                        </div>

                        <!-- Upload Controls -->
                        <div class="flex flex-col gap-1.5">
                            <div class="relative">
                                <input type="file" name="logo" id="logo_input" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml"
                                    @change="fileChosen($event)" class="hidden">
                                <label for="logo_input" 
                                    class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 active:bg-slate-100 cursor-pointer shadow-sm transition-all duration-150 hover:border-slate-350 select-none">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Choisir une image
                                </label>
                            </div>
                            <p class="text-[9px] text-slate-400">Format PNG, JPG, JPEG, WEBP, GIF ou SVG (Max. 4 Mo)</p>
                        </div>
                    </div>
                    
                    <input type="hidden" name="remove_logo" :value="shouldRemove ? '1' : '0'">
                    @error('logo') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Paramètres Système & Comptables --}}
                <div class="md:col-span-2 mt-4">
                    <h2 class="text-sm font-semibold text-slate-800 mb-4">Système & Comptabilité</h2>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Devise <span class="text-red-500">*</span></label>
                    <input type="text" name="currency" value="{{ old('currency', $settings->currency ?? 'FCFA') }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('currency') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Taux de TVA (%) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate ?? 17.5) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('tax_rate') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Préfixe des Factures <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix ?? 'FAC') }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('invoice_prefix') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                </div>

                {{-- Type de Commerce --}}
                <div class="md:col-span-2 mt-4 pt-4 border-t border-slate-100">
                    <h2 class="text-sm font-semibold text-slate-800 mb-2">Type de commerce & Activité</h2>
                    <p class="text-xs text-slate-400 mb-4">Ce choix adapte les libellés, les catégories proposées et certaines unités sans modifier la logique de gestion.</p>
                </div>

                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ businessType: '{{ old('business_type', $settings->business_type ?? 'quincaillerie') }}' }">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Type d'activité <span class="text-red-500">*</span></label>
                        <select name="business_type" x-model="businessType"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            <option value="quincaillerie">Quincaillerie</option>
                            <option value="boutique_generale">Boutique générale</option>
                            <option value="superette">Superette</option>
                            <option value="pieces_detachees">Pièces détachées</option>
                            <option value="cosmetique">Cosmétique</option>
                            <option value="pharmacie_parapharmacie">Pharmacie / Parapharmacie</option>
                            <option value="informatique">Informatique</option>
                            <option value="electromenager">Électroménager</option>
                            <option value="depot_grossiste">Dépôt / Grossiste</option>
                            <option value="autre">Autre</option>
                        </select>
                        @error('business_type') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div x-show="businessType === 'autre'">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nom du type d'activité personnalisé</label>
                        <input type="text" name="business_type_custom" value="{{ old('business_type_custom', $settings->business_type_custom ?? '') }}"
                            placeholder="Ex: Librairie, Boutique de vêtements"
                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        @error('business_type_custom') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Aperçu du libellé utilisé --}}
                <div class="md:col-span-2 bg-slate-50 border border-slate-200 rounded-lg p-4 mt-2">
                    <p class="text-xs font-bold text-slate-800 mb-2">Aperçu de la configuration active :</p>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-600 mb-3">
                        <li>Votre logiciel est configuré pour : <span class="font-bold text-slate-800">{{ app(\App\Services\BusinessTypeService::class)->label() }}</span></li>
                        <li>Les catégories proposées seront : <span class="font-bold text-slate-800">{{ implode(', ', app(\App\Services\BusinessTypeService::class)->defaultCategories()) }}</span></li>
                    </ul>
                    <hr class="border-slate-250/60 my-2">
                    <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[10px] text-slate-500">
                        <li><span class="font-medium text-slate-700">Libellé :</span> {{ app(\App\Services\BusinessTypeService::class)->label() }}</li>
                        <li><span class="font-medium text-slate-700">Produit :</span> {{ app(\App\Services\BusinessTypeService::class)->productLabel() }}</li>
                        <li><span class="font-medium text-slate-700">Catégorie :</span> {{ app(\App\Services\BusinessTypeService::class)->categoryLabel() }}</li>
                        <li><span class="font-medium text-slate-700">Fournisseur :</span> {{ app(\App\Services\BusinessTypeService::class)->supplierLabel() }}</li>
                    </ul>
                </div>

            </div>

            {{-- Bouton de validation --}}
            <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit"  class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Enregistrer les modifications
                </button>
            </div>
        </form>

        {{-- Section Initialisation du commerce --}}
        <div class="mt-8 pt-6 border-t border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800 mb-1">Initialisation du commerce</h2>
            <p class="text-xs text-slate-400 mb-4">
                Vous pouvez configurer les catégories recommandées pour votre type d'activité.
                Cette action n'effacera pas vos catégories existantes et évitera les doublons.
            </p>
            <a href="{{ route('admin.settings.default-categories') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-slate-950 text-xs font-bold rounded-lg transition shadow-sm select-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Proposer les catégories par défaut
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function logoUploader() {
        return {
            previewUrl: '{{ $settings && $settings->logo ? asset("storage/" . $settings->logo) : "" }}',
            shouldRemove: false,
            
            fileChosen(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewUrl = e.target.result;
                    this.shouldRemove = false;
                };
                reader.readAsDataURL(file);
            },
            
            removeLogo() {
                this.previewUrl = '';
                this.shouldRemove = true;
                const fileInput = document.getElementById('logo_input');
                if (fileInput) fileInput.value = '';
            }
        };
    }
</script>
@endpush
@endsection
