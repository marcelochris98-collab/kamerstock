<!DOCTYPE html>
<html lang="fr" x-data="onboardingForm()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KamerStock — Créer votre boutique</title>
    <meta name="description" content="Inscrivez votre commerce sur KamerStock en 2 minutes. 14 jours d'essai gratuit avec base de données dédiée et caisse POS.">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-outfit { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }

        .linear-grid {
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        }

        .field-pair {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .field-pair {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-amber-500 selection:text-slate-950 flex flex-col justify-between">

    <div class="min-h-screen w-full flex flex-col lg:flex-row">

        <!-- ========================================================================= -->
        <!-- VOLET GAUCHE : BRANDING, FORCES CLÉS & FILIGRANE (OMNIVAULT STYLE)       -->
        <!-- ========================================================================= -->
        <aside class="relative lg:w-1/2 xl:w-[46%] bg-[#06090e] border-b lg:border-b-0 lg:border-r border-white/[0.06] p-8 sm:p-12 lg:p-16 flex flex-col justify-between overflow-hidden">
            
            <!-- Trame de fond subtile -->
            <div class="absolute inset-0 linear-grid pointer-events-none opacity-60"></div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Grand Filigrane / Watermark Emblème KamerStock (Shield + Keyhole) -->
            <div class="absolute -right-16 -bottom-16 w-[420px] h-[420px] pointer-events-none select-none opacity-[0.035] text-white">
                <svg viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="6" class="w-full h-full">
                    <circle cx="100" cy="100" r="90" stroke-width="5"/>
                    <path d="M100 35 L155 60 V105 C155 140 100 170 100 170 C100 170 45 140 45 105 V60 Z" stroke-width="7" stroke-linejoin="round"/>
                    <circle cx="100" cy="95" r="14" fill="currentColor"/>
                    <path d="M94 105 L90 135 H110 L106 105 Z" fill="currentColor"/>
                </svg>
            </div>

            <!-- 1. En-tête gauche : Logo & Nom de marque -->
            <div class="relative z-10 flex items-center gap-3">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 via-amber-500 to-amber-600 flex items-center justify-center font-black text-slate-950 font-outfit text-xl shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform duration-200">
                        K
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-black tracking-wider text-white font-outfit uppercase">KamerStock</span>
                        <span class="text-[10px] text-amber-400 font-mono tracking-widest uppercase -mt-0.5">Commercial SaaS</span>
                    </div>
                </a>
            </div>

            <!-- 2. Cœur gauche : Accroche & 3 Piliers réels de KamerStock (Sans IA) -->
            <div class="relative z-10 my-10 lg:my-auto max-w-lg">
                
                <!-- Badge capsule -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/[0.04] text-slate-300 border border-white/[0.08] backdrop-blur-md mb-6 shadow-inner">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>14 jours d'essai gratuit · Sans carte bancaire</span>
                </div>

                <!-- Grand Titre -->
                <h1 class="font-outfit font-black text-3xl sm:text-4xl lg:text-[44px] tracking-tight text-white leading-[1.15]">
                    Votre commerce,<br>
                    <span class="text-slate-400">enfin sous contrôle.</span>
                </h1>

                <!-- Sous-titre descriptif -->
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed mt-4 mb-8">
                    Déployez votre espace de gestion commerciale en 2 minutes. Vos données sont 100% étanches et isolées.
                </p>

                <!-- Les 3 Cartes Fonctionnalités Réelles -->
                <div class="space-y-4">
                    
                    <!-- Pilier 1 : Stock & Dépôts -->
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.05] hover:border-white/[0.1] hover:bg-white/[0.04] transition duration-200">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white font-outfit">Stock en temps réel</h2>
                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                Entrées, sorties, inventaires et alertes de rupture sur tous vos dépôts.
                            </p>
                        </div>
                    </div>

                    <!-- Pilier 2 : Ventes & Facturation -->
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.05] hover:border-white/[0.1] hover:bg-white/[0.04] transition duration-200">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white font-outfit">Ventes & factures</h2>
                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                Caisse POS express, tickets thermiques et devis/factures PDF à votre logo en FCFA.
                            </p>
                        </div>
                    </div>

                    <!-- Pilier 3 : Crédits Clients & Marges (Remplacement de l'IA) -->
                    <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/[0.02] border border-white/[0.05] hover:border-white/[0.1] hover:bg-white/[0.04] transition duration-200">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white font-outfit">Crédits clients & rentabilité</h2>
                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                Plafonds autorisés, blocage automatique des créances et suivi précis de vos marges.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- 3. Pied gauche : Copyright & Ville -->
            <div class="relative z-10 pt-6 border-t border-white/[0.04] flex items-center justify-between text-xs text-slate-500">
                <span>&copy; {{ date('Y') }} KamerStock. Tous droits réservés.</span>
                <span class="font-mono text-[11px] text-slate-600">Douala, Cameroun</span>
            </div>

        </aside>

        <!-- ========================================================================= -->
        <!-- VOLET DROIT : FORMULAIRE D'INSCRIPTION ÉPURÉ                              -->
        <!-- ========================================================================= -->
        <main class="lg:w-1/2 xl:w-[54%] flex flex-col justify-between p-6 sm:p-10 lg:p-12 transition-colors duration-200 overflow-y-auto"
              :class="darkMode ? 'bg-[#0a0e17] text-slate-100' : 'bg-white text-slate-900'">
            
            <!-- Barre supérieure : Retour & Thème toggle -->
            <div class="w-full max-w-xl mx-auto flex items-center justify-between">
                <a href="{{ url('/') }}" 
                   class="inline-flex items-center gap-2 text-xs font-medium transition"
                   :class="darkMode ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Retour au site</span>
                </a>

                <!-- Bascule Mode Sombre / Clair -->
                <button type="button" 
                        @click="darkMode = !darkMode"
                        class="p-2 rounded-xl transition border"
                        :class="darkMode ? 'bg-white/[0.04] border-white/[0.08] text-amber-400 hover:bg-white/[0.08]' : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200'"
                        :title="darkMode ? 'Passer en mode clair' : 'Passer en mode sombre'">
                    <template x-if="darkMode">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </template>
                    <template x-if="!darkMode">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </template>
                </button>
            </div>

            <!-- Conteneur centré du formulaire -->
            <div class="w-full max-w-xl mx-auto my-auto py-6">
                
                <!-- Icône centrale de marque -->
                <div class="flex justify-center mb-4">
                    <div class="w-13 h-13 p-3 rounded-2xl flex items-center justify-center shadow-xl border"
                         :class="darkMode ? 'bg-gradient-to-br from-amber-500/20 to-amber-500/5 border-amber-500/30 text-amber-400 shadow-amber-500/5' : 'bg-amber-50 border-amber-200 text-amber-600 shadow-amber-100'">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>

                <!-- Titre & Sous-titre -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl sm:text-3xl font-black font-outfit tracking-tight"
                        :class="darkMode ? 'text-white' : 'text-slate-900'">
                        Créer votre boutique
                    </h2>
                    <p class="text-xs sm:text-sm mt-1"
                       :class="darkMode ? 'text-slate-400' : 'text-slate-500'">
                        Lancez votre boutique en 2 minutes. 14 jours d'essai gratuit sans engagement.
                    </p>
                </div>

                <!-- Messages d'erreurs globaux -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-xl text-xs border flex items-start gap-3"
                         :class="darkMode ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' : 'bg-rose-50 border-rose-200 text-rose-700'">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <div class="font-bold mb-0.5">Veuillez vérifier les champs indiqués :</div>
                            <ul class="list-disc pl-4 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Formulaire d'Inscription -->
                <form method="POST" action="{{ route('register') }}" class="space-y-3.5" @submit="isSubmitting = true">
                    @csrf

                    <!-- LIGNE 1 : Nom de la boutique & Secteur d'activité -->
                    <div class="field-pair">
                        <!-- Nom Boutique -->
                        <div>
                            <label for="shop_name" 
                                   class="block text-xs font-semibold mb-1"
                                   :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                Nom du commerce ou boutique <span class="text-amber-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="shop_name" 
                                       name="shop_name" 
                                       required
                                       autofocus
                                       placeholder="EX : supermarche des jeux" 
                                       x-model="shopName"
                                       @input="updateSlug()"
                                       value="{{ old('shop_name') }}"
                                       class="w-full pl-9 pr-3 py-2 rounded-xl text-xs sm:text-sm transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                       :class="darkMode ? 'bg-slate-900/90 border-slate-800 text-white placeholder-slate-500' : 'bg-slate-50/80 border-slate-200 text-slate-900 placeholder-slate-400'">
                            </div>
                            <x-input-error :messages="$errors->get('shop_name')" class="mt-1 text-[11px]" />
                        </div>

                        <!-- Secteur d'activité (Saisie libre + Suggestions rapides) -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="business_type" 
                                       class="block text-xs font-semibold"
                                       :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                    Secteur d'activité <span class="text-[11px] font-normal text-slate-400">(libre ou suggéré)</span>
                                </label>
                            </div>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="business_type" 
                                       name="business_type" 
                                       x-model="businessType"
                                       list="business_type_suggestions"
                                       placeholder="Ex: Quincaillerie, Menuiserie, Restaurant..."
                                       value="{{ old('business_type') }}"
                                       class="w-full pl-9 pr-3 py-2 rounded-xl text-xs sm:text-sm transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                       :class="darkMode ? 'bg-slate-900/90 border-slate-800 text-white placeholder-slate-500' : 'bg-slate-50/80 border-slate-200 text-slate-900 placeholder-slate-400'">

                                <datalist id="business_type_suggestions">
                                    @foreach($businessTypes as $key => $type)
                                        <option value="{{ $type['label'] ?? ucfirst($key) }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <x-input-error :messages="$errors->get('business_type')" class="mt-1 text-[11px]" />
                        </div>
                    </div>

                    <!-- Suggestions rapides cliquables (Pills) -->
                    <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                        <span class="text-[10px] font-medium text-slate-500 select-none mr-0.5">Suggestions rapides :</span>
                        @php
                            $quickSectors = [
                                'Quincaillerie', 
                                'Supérette', 
                                'Cosmétique', 
                                'Prêt-à-porter',
                                'Pharmacie',
                                'Informatique', 
                                'Dépôt / Grossiste',
                                'Électroménager',
                                'Menuiserie'
                            ];
                        @endphp
                        @foreach($quickSectors as $sector)
                            <button type="button" 
                                    @click="businessType = (businessType.toLowerCase() === '{{ strtolower($sector) }}' ? '' : '{{ $sector }}')"
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-medium transition select-none border cursor-pointer"
                                    :class="businessType.toLowerCase() === '{{ strtolower($sector) }}'
                                        ? (darkMode ? 'bg-amber-500/20 border-amber-500/50 text-amber-300 shadow-sm' : 'bg-amber-100 border-amber-300 text-amber-800')
                                        : (darkMode ? 'bg-white/[0.03] border-white/[0.06] text-slate-400 hover:text-white hover:bg-white/[0.06]' : 'bg-slate-100 border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-200')">
                                {{ $sector }}
                            </button>
                        @endforeach
                    </div>

                    <!-- LIGNE 2 : Identifiant Web / Slug en direct -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="slug" 
                                   class="text-xs font-semibold"
                                   :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                Adresse web dédiée de votre boutique
                            </label>
                            
                            <!-- Statut en direct -->
                            <div class="text-[11px] font-mono flex items-center gap-1">
                                <template x-if="slugStatus === 'checking'">
                                    <span class="text-slate-400">◌ Vérification...</span>
                                </template>
                                <template x-if="slugStatus === 'available'">
                                    <span class="text-emerald-500 font-semibold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Disponible
                                    </span>
                                </template>
                                <template x-if="slugStatus === 'taken'">
                                    <span class="text-rose-400 font-semibold" x-text="slugMessage"></span>
                                </template>
                            </div>
                        </div>

                        <div class="relative flex items-center">
                            <span class="absolute left-3.5 text-xs font-mono text-slate-500 pointer-events-none select-none">
                                kamerstock.cm/t/
                            </span>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   x-model="slug"
                                   @input="checkSlugAvailability()"
                                   value="{{ old('slug') }}"
                                   placeholder="identifiant-boutique"
                                   class="w-full bg-transparent pl-32 pr-3 py-2 rounded-xl text-xs sm:text-sm font-mono text-amber-500 transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                   :class="darkMode ? 'bg-slate-900/90 border-slate-800 placeholder-slate-600' : 'bg-slate-50/80 border-slate-200 placeholder-slate-400'">
                        </div>
                        <x-input-error :messages="$errors->get('slug')" class="mt-1 text-[11px]" />
                    </div>

                    <!-- LIGNE 3 : Nom Gérant & Email Professionnel -->
                    <div class="field-pair">
                        <!-- Nom complet -->
                        <div>
                            <label for="name" 
                                   class="block text-xs font-semibold mb-1"
                                   :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                Votre nom complet <span class="text-amber-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Marcel Tchamba"
                                       class="w-full pl-9 pr-3 py-2 rounded-xl text-xs sm:text-sm transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                       :class="darkMode ? 'bg-slate-900/90 border-slate-800 text-white placeholder-slate-500' : 'bg-slate-50/80 border-slate-200 text-slate-900 placeholder-slate-400'">
                            </div>
                            <x-input-error :messages="$errors->get('name')" class="mt-1 text-[11px]" />
                        </div>

                        <!-- Email pro -->
                        <div>
                            <label for="email" 
                                   class="block text-xs font-semibold mb-1"
                                   :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                Email professionnel <span class="text-amber-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </div>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required
                                       value="{{ old('email') }}"
                                       placeholder="gerant@commerce.com"
                                       class="w-full pl-9 pr-3 py-2 rounded-xl text-xs sm:text-sm transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                       :class="darkMode ? 'bg-slate-900/90 border-slate-800 text-white placeholder-slate-500' : 'bg-slate-50/80 border-slate-200 text-slate-900 placeholder-slate-400'">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1 text-[11px]" />
                        </div>
                    </div>

                    <!-- LIGNE 4 : Mot de passe & Confirmation avec toggles œil -->
                    <div class="field-pair">
                        <!-- Mot de passe -->
                        <div>
                            <label for="password" 
                                   class="block text-xs font-semibold mb-1"
                                   :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                Mot de passe <span class="text-amber-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password" 
                                       name="password" 
                                       required
                                       x-model="password"
                                       autocomplete="new-password"
                                       placeholder="8 caractères min."
                                       class="w-full pl-9 pr-9 py-2 rounded-xl text-xs sm:text-sm transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                       :class="darkMode ? 'bg-slate-900/90 border-slate-800 text-white placeholder-slate-500' : 'bg-slate-50/80 border-slate-200 text-slate-900 placeholder-slate-400'">
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-amber-400 transition"
                                        aria-label="Afficher ou masquer le mot de passe">
                                    <template x-if="!showPassword">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </template>
                                    <template x-if="showPassword">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                    </template>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1 text-[11px]" />
                        </div>

                        <!-- Confirmation -->
                        <div>
                            <label for="password_confirmation" 
                                   class="block text-xs font-semibold mb-1"
                                   :class="darkMode ? 'text-slate-300' : 'text-slate-700'">
                                Confirmer mot de passe <span class="text-amber-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <input :type="showConfirmPassword ? 'text' : 'password'" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       x-model="passwordConfirmation"
                                       autocomplete="new-password"
                                       placeholder="Répétez le mot de passe"
                                       class="w-full pl-9 pr-9 py-2 rounded-xl text-xs sm:text-sm transition duration-150 border focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"
                                       :class="darkMode ? 'bg-slate-900/90 border-slate-800 text-white placeholder-slate-500' : 'bg-slate-50/80 border-slate-200 text-slate-900 placeholder-slate-400'">
                                <button type="button" 
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-amber-400 transition"
                                        aria-label="Afficher ou masquer la confirmation du mot de passe">
                                    <template x-if="!showConfirmPassword">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </template>
                                    <template x-if="showConfirmPassword">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                        </svg>
                                    </template>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-[11px]" />
                        </div>
                    </div>

                    <!-- LIGNE 5 : Conditions d'utilisation -->
                    <div class="pt-1">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input type="checkbox" 
                                   id="terms" 
                                   name="terms" 
                                   required
                                   checked
                                   class="w-4 h-4 mt-0.5 rounded text-amber-500 focus:ring-amber-500"
                                   :class="darkMode ? 'bg-slate-900 border-slate-700' : 'bg-white border-slate-300'">
                            <span class="text-xs leading-snug" :class="darkMode ? 'text-slate-400' : 'text-slate-600'">
                                J'accepte les conditions et je démarre mes <strong class="text-amber-500 font-semibold">14 jours d'essai gratuit</strong>.
                            </span>
                        </label>
                        <x-input-error :messages="$errors->get('terms')" class="mt-1 text-[11px]" />
                    </div>

                    <!-- Bouton de Soumission : Lancer ma boutique gratuitement -> -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="w-full py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-slate-950 bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-lg shadow-amber-500/20 active:scale-[0.99] transition duration-150 flex items-center justify-center gap-2 font-outfit disabled:opacity-75 disabled:cursor-wait">
                            <template x-if="!isSubmitting">
                                <span class="flex items-center gap-2">
                                    <span>Lancer ma boutique gratuitement</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </span>
                            </template>
                            <template x-if="isSubmitting">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Déploiement de votre base de données...</span>
                                </span>
                            </template>
                        </button>
                    </div>

                    <!-- Mention sécurité réassurance -->
                    <div class="text-center pt-1">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium"
                              :class="darkMode ? 'text-slate-400' : 'text-slate-500'">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Base de données MySQL dédiée et isolée pour chaque boutique
                        </span>
                    </div>

                </form>

                <!-- Séparateur "Besoin d'aide ?" (comme dans la capture) -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t" :class="darkMode ? 'border-white/[0.08]' : 'border-slate-200'"></div>
                    </div>
                    <div class="relative flex justify-center text-[11px] uppercase tracking-wider font-semibold">
                        <span class="px-3" :class="darkMode ? 'bg-[#0a0e17] text-slate-500' : 'bg-white text-slate-400'">
                            Besoin d'aide ?
                        </span>
                    </div>
                </div>

                <!-- 3 Boutons d'aide : WhatsApp, Telegram, Support direct -->
                <div class="grid grid-cols-3 gap-2.5">
                    
                    <!-- WhatsApp -->
                    <a href="https://wa.me/237600000000?text=Bonjour%20KamerStock,%20j'ai%20besoin%20d'aide%20pour%20créer%20ma%20boutique" 
                       target="_blank" 
                       rel="noopener"
                       class="flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-xl text-[11px] font-semibold border transition duration-150"
                       :class="darkMode ? 'bg-white/[0.03] border-white/[0.08] text-slate-300 hover:text-white hover:bg-white/[0.06] hover:border-emerald-500/40' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-emerald-500/40'">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.173.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.072.043.419-.101.824z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>

                    <!-- Telegram -->
                    <a href="https://t.me/kamerstock" 
                       target="_blank" 
                       rel="noopener"
                       class="flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-xl text-[11px] font-semibold border transition duration-150"
                       :class="darkMode ? 'bg-white/[0.03] border-white/[0.08] text-slate-300 hover:text-white hover:bg-white/[0.06] hover:border-sky-500/40' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-sky-500/40'">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .36z"/>
                        </svg>
                        <span>Telegram</span>
                    </a>

                    <!-- Support Direct / Documentation -->
                    <a href="{{ url('/') }}#faq" 
                       class="flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-xl text-[11px] font-semibold border transition duration-150"
                       :class="darkMode ? 'bg-white/[0.03] border-white/[0.08] text-slate-300 hover:text-white hover:bg-white/[0.06] hover:border-amber-500/40' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100 hover:border-amber-500/40'">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Guide & FAQ</span>
                    </a>

                </div>

                <!-- Bascule vers la Connexion -->
                <div class="mt-6 text-center text-xs" :class="darkMode ? 'text-slate-400' : 'text-slate-600'">
                    Vous avez déjà une boutique enregistrée ?
                    <a href="{{ route('login') }}" class="font-bold text-amber-500 hover:text-amber-400 ml-1 transition">
                        Se connecter &rarr;
                    </a>
                </div>

            </div>

            <!-- Footer discret côté droit -->
            <div class="w-full max-w-xl mx-auto pt-4 flex items-center justify-between text-[11px]"
                 :class="darkMode ? 'text-slate-600' : 'text-slate-400'">
                <a href="{{ route('landlord.login') }}" class="hover:text-amber-500 transition inline-flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                    <span>Console Landlord</span>
                </a>
                <span>KamerStock SaaS &bull; Douala</span>
            </div>

        </main>

    </div>

    <!-- Alpine.js Onboarding Logic -->
    <script>
        function onboardingForm() {
            return {
                darkMode: true,
                showPassword: false,
                showConfirmPassword: false,
                shopName: '{{ old('shop_name', '') }}',
                slug: '{{ old('slug', '') }}',
                businessType: '{{ old('business_type', '') }}',
                password: '',
                passwordConfirmation: '',
                slugStatus: 'idle',
                slugMessage: '',
                isSubmitting: false,
                slugTimer: null,

                init() {
                    if (this.shopName && !this.slug) {
                        this.updateSlug();
                    }
                },

                updateSlug() {
                    const cleanSlug = this.shopName
                        .toLowerCase()
                        .trim()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');

                    this.slug = cleanSlug;
                    this.checkSlugAvailability();
                },

                checkSlugAvailability() {
                    clearTimeout(this.slugTimer);
                    if (!this.slug) {
                        this.slugStatus = 'idle';
                        this.slugMessage = '';
                        return;
                    }

                    this.slugStatus = 'checking';

                    this.slugTimer = setTimeout(() => {
                        fetch(`{{ route('check.slug') }}?slug=${encodeURIComponent(this.slug)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.available) {
                                    this.slugStatus = 'available';
                                    this.slugMessage = 'Disponible';
                                } else {
                                    this.slugStatus = 'taken';
                                    this.slugMessage = data.message || 'Déjà pris';
                                }
                            })
                            .catch(() => {
                                this.slugStatus = 'idle';
                            });
                    }, 350);
                }
            }
        }
    </script>
</body>
</html>
