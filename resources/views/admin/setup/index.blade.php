@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium">Administration / Assistant</p>
            <h1 class="text-xl font-bold text-slate-900 mt-1">Configuration initiale de la boutique</h1>
            <p class="text-xs text-slate-500 mt-0.5">Personnalisez KamerStock selon votre activité en quelques clics.</p>
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
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-250 text-emerald-700 rounded-xl text-xs font-medium shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Configuration Forms --}}
        <div class="lg:col-span-2 space-y-6">

            <form action="{{ route('admin.setup.store') }}" method="POST" enctype="multipart/form-data">
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

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Logo boutique</label>
                            @if($settings->logo)
                                <div class="flex items-center gap-3 mb-2">
                                    <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="w-10 h-10 rounded object-cover border">
                                    <span class="text-[10px] text-slate-400">Un nouveau fichier remplacera l'existant.</span>
                                </div>
                            @endif
                            <input type="file" name="logo" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            @error('logo') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
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
                                onchange="window.location.href = '{{ route('admin.setup.index') }}?business_type=' + this.value"
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
                    <p class="text-xs text-slate-400 mb-4">Seules les catégories cochées et inexistantes seront créées.</p>

                    <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        @forelse($categories as $category)
                            <label class="flex items-center justify-between p-2.5 border rounded-lg {{ $category['exists'] ? 'bg-slate-50 text-slate-450 border-slate-150' : 'bg-white text-slate-700 border-slate-200 hover:border-amber-400 cursor-pointer transition' }}">
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
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Enregistrer l'étape de configuration
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
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded text-[10px] font-bold">Étape Validée</span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-600 rounded text-[10px] font-bold">En cours</span>
                            @endif
                        </span>
                    </li>
                </ul>

                @if($settings->setup_step === 'configured' || $settings->setup_step === 'completed')
                    <form action="{{ route('admin.setup.finish') }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Terminer la configuration
                        </button>
                    </form>
                @else
                    <button disabled class="w-full py-2.5 bg-slate-100 text-slate-400 text-xs font-bold rounded-lg cursor-not-allowed mb-3">
                        Renseigner et enregistrer d'abord
                    </button>
                @endif

                @if($settings->setup_completed)
                    <form action="{{ route('admin.setup.reset') }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment réinitialiser l\'assistant de configuration ? Les données créées (catégories, etc.) ne seront pas effacées.')">
                        @csrf
                        <button type="submit" class="w-full py-2 border border-red-200 text-red-500 hover:bg-red-50 text-xs font-semibold rounded-lg transition">
                            Réinitialiser l'assistant
                        </button>
                    </form>
                @endif

            </div>

        </div>

    </div>

</div>
@endsection
