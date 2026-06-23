@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium">Administration / Paramètres</p>
            <h1 class="text-xl font-bold text-slate-900 mt-1">Configuration du commerce ({{ app(\App\Services\BusinessTypeService::class)->label() }})</h1>
        </div>
        <div>
            @if($settings->setup_completed)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-200 rounded-full text-xs font-bold text-emerald-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Configuration terminée
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 rounded-full text-xs font-bold text-amber-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    En attente de configuration
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-medium shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Configuration Forms --}}
        <div class="lg:col-span-2 space-y-6">

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                {{-- 1. Informations Générales --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-7 h-7 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-xs">1</span>
                        <h2 class="text-sm font-bold text-slate-800">Informations de la Boutique</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nom de la boutique <span class="text-red-500">*</span></label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $settings->shop_name ?? '') }}"
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

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Devise <span class="text-red-500">*</span></label>
                            <input type="text" name="currency" value="{{ old('currency', $settings->currency ?? 'FCFA') }}"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @error('currency') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Préfixe des Factures <span class="text-red-500">*</span></label>
                            <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix ?? 'FAC') }}"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @error('invoice_prefix') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Taux de TVA (%) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate ?? 17.5) }}"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @error('tax_rate') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2" x-data="logoUploader()">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Logo boutique</label>
                            
                            <div class="flex items-center gap-4 mt-2 mb-3">
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
                    </div>
                </div>

                {{-- 2. Type d'Activité --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-7 h-7 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-xs">2</span>
                        <h2 class="text-sm font-bold text-slate-800">Secteur d'Activité</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ type: '{{ old('business_type', $businessType) }}' }">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Type de commerce</label>
                            <select name="business_type" x-model="type"
                                onchange="window.location.href = '{{ route('admin.settings.index') }}?business_type=' + this.value"
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

                        <div x-show="type === 'autre'">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Activité personnalisée</label>
                            <input type="text" name="business_type_custom" value="{{ old('business_type_custom', $settings->business_type_custom ?? '') }}"
                                placeholder="Ex: Librairie"
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            @error('business_type_custom') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- 3. Catégories Recommandées --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-7 h-7 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-xs">3</span>
                        <h2 class="text-sm font-bold text-slate-800">Catégories suggérées</h2>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Seules les catégories cochées et inexistantes seront créées lors de l'enregistrement.</p>

                    <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        @forelse($categories as $category)
                            <label class="flex items-center justify-between p-2.5 border rounded-lg {{ $category['exists'] ? 'bg-slate-50 text-slate-400 border-slate-150' : 'bg-white text-slate-700 border-slate-200 hover:border-amber-400 cursor-pointer transition' }}">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="categories[]" value="{{ $category['name'] }}"
                                        {{ $category['exists'] ? '' : 'checked' }}
                                        class="rounded text-amber-500 focus:ring-amber-500 border-slate-300 h-4 w-4">
                                    <span class="text-xs font-semibold {{ $category['exists'] ? 'line-through text-slate-400' : '' }}">
                                        {{ $category['name'] }}
                                    </span>
                                </div>
                                @if($category['exists'])
                                    <span class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[9px] font-bold rounded">Déjà existante</span>
                                @else
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[9px] font-bold rounded">Nouveau</span>
                                @endif
                            </label>
                        @empty
                            <p class="text-xs text-slate-450 text-center py-4">Pas de suggestions spécifiques pour cette activité. Vous pourrez créer des catégories librement.</p>
                        @endforelse
                    </div>
                </div>

                {{-- 4. Unités de stock --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 mb-6">
                    <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-7 h-7 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-xs">4</span>
                        <h2 class="text-sm font-bold text-slate-800">Unités de Stock à Activer</h2>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Cochez les unités de stock dont vous avez besoin. Les unités recommandées pour l'activité choisie sont cochées par défaut.</p>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($allUnits as $key => $label)
                            @php
                                $isRecommended = in_array($key, $recommendedUnits);
                                $isChecked = is_array($settings->enabled_units) ? in_array($key, $settings->enabled_units) : $isRecommended;
                            @endphp
                            <label class="flex items-center justify-between p-2.5 border border-slate-200 rounded-lg hover:border-amber-400 cursor-pointer bg-white transition select-none">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="units[]" value="{{ $key }}"
                                        {{ $isChecked ? 'checked' : '' }}
                                        class="rounded text-amber-500 focus:ring-amber-500 border-slate-300 h-4 w-4">
                                    <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                                </div>
                                @if($isRecommended)
                                    <span class="px-1.5 py-0.5 bg-amber-50 text-amber-700 text-[8px] font-bold rounded">Recommandé</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Action save button --}}
                <div class="flex justify-end">
                    <button type="submit" class="w-full px-6 py-2.5 bg-slate-900 hover:bg-slate-850 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Enregistrer les modifications
                    </button>
                </div>

            </form>
        </div>

        {{-- Summary & Validation Sidebar --}}
        <div class="space-y-6">

            <div class="bg-white border border-slate-200 shadow-sm rounded-xl p-6 sticky top-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Résumé & Finalisation</h3>
                
                <ul class="space-y-4 mb-6">
                    <li class="flex justify-between items-start gap-4 text-xs">
                        <span class="text-slate-400">Boutique</span>
                        <span class="font-semibold text-slate-800 text-right">{{ $settings->shop_name ?: 'KamerStock' }}</span>
                    </li>
                    <li class="flex justify-between items-start gap-4 text-xs">
                        <span class="text-slate-400">Activité</span>
                        <span class="font-semibold text-slate-800 text-right">
                            @if($businessType === 'autre')
                                Autre ({{ $settings->business_type_custom ?: 'Commerce' }})
                            @else
                                {{ $config['label'] ?? 'Non configuré' }}
                            @endif
                        </span>
                    </li>
                    <li class="flex justify-between items-start gap-4 text-xs">
                        <span class="text-slate-400">Unités activées</span>
                        <span class="font-semibold text-slate-800 text-right">
                            {{ is_array($settings->enabled_units) ? count($settings->enabled_units) . ' unité(s)' : 'Par défaut' }}
                        </span>
                    </li>
                    <li class="flex justify-between items-start gap-4 text-xs">
                        <span class="text-slate-400">Statut de l'étape</span>
                        <span>
                            @if($settings->setup_step === 'configured' || $settings->setup_step === 'completed')
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-bold">Configuré</span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[10px] font-bold">En cours</span>
                            @endif
                        </span>
                    </li>
                </ul>

                @if($settings->setup_step === 'configured' || $settings->setup_step === 'completed')
                    <form action="{{ route('admin.settings.finish') }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-1.5 select-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Terminer la configuration
                        </button>
                    </form>
                @else
                    <button disabled class="w-full py-2.5 bg-slate-100 text-slate-400 text-xs font-bold rounded-lg cursor-not-allowed mb-3 select-none">
                        Renseigner et enregistrer d'abord
                    </button>
                @endif

                @if($settings->setup_completed)
                    <form action="{{ route('admin.settings.reset') }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment réinitialiser l\'assistant de configuration ? Les données créées (catégories, etc.) ne seront pas effacées.')">
                        @csrf
                        <button type="submit" class="w-full py-2 border border-red-200 text-red-500 hover:bg-red-50 text-xs font-semibold rounded-lg transition select-none">
                            Réinitialiser l'assistant
                        </button>
                    </form>
                @endif

            </div>

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
