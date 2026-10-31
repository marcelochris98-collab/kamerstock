<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créer votre boutique — KamerStock SaaS</title>
    <meta name="description" content="Inscrivez votre boutique en libre-service sur KamerStock en 2 minutes. 14 jours d'essai gratuit avec base de données dédiée et caisse POS.">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            letter-spacing: -0.011em;
        }
        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }
        [x-cloak] { display: none !important; }

        .linear-grid {
            background-size: 50px 50px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-full bg-slate-950 text-slate-100 antialiased selection:bg-amber-500 selection:text-slate-950 relative overflow-x-hidden"
      x-data="onboardingForm()">

    <!-- Ambient Grid & Glow -->
    <div class="fixed inset-0 linear-grid pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-gradient-to-b from-amber-500/10 via-amber-500/5 to-transparent blur-3xl pointer-events-none z-0"></div>

    <div class="relative z-10 min-h-screen flex flex-col justify-between">
        
        <!-- Top Minimal Header -->
        <header class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center font-black text-slate-950 font-outfit text-base tracking-tight shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform duration-200">
                    K
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-white tracking-tight font-outfit">KamerStock</span>
                    <span class="px-1.5 py-0.5 text-[9px] font-semibold text-amber-400 bg-amber-500/10 border border-amber-500/20 rounded">Onboarding</span>
                </div>
            </a>

            <div class="text-xs text-slate-400">
                Vous avez déjà une boutique ?
                <a href="{{ route('login') }}" class="font-semibold text-amber-400 hover:text-amber-300 ml-1 transition">
                    Se connecter &rarr;
                </a>
            </div>
        </header>

        <!-- Main Onboarding Content (2 Columns) -->
        <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 my-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                
                <!-- Left Column : Registration Form -->
                <div class="lg:col-span-7 bg-slate-900/60 border border-white/[0.08] backdrop-blur-xl rounded-2xl p-6 sm:p-10 shadow-2xl shadow-black/60">
                    
                    <div class="mb-8 space-y-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            Essai gratuit de 14 jours inclus
                        </span>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-outfit tracking-tight">
                            Lancez votre commerce en 2 minutes
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-400 font-light">
                            Créez votre espace de travail autonome. Votre base de données sera isolée et configurée instantanément.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs">
                            <div class="font-bold mb-1 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Veuillez corriger les informations suivantes :</span>
                            </div>
                            <ul class="list-disc pl-5 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="space-y-6" @submit="isSubmitting = true">
                        @csrf

                        <!-- SECTION 1 : VOTRE BOUTIQUE -->
                        <div class="space-y-4">
                            <div class="text-[11px] font-mono uppercase tracking-wider text-amber-400/90 font-semibold flex items-center gap-2">
                                <span>01. Votre Entreprise</span>
                                <span class="h-px bg-slate-800 flex-1"></span>
                            </div>

                            <!-- Nom de la Boutique -->
                            <div>
                                <label for="shop_name" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                    Nom de votre commerce ou boutique <span class="text-amber-500">*</span>
                                </label>
                                <input type="text" 
                                       id="shop_name" 
                                       name="shop_name" 
                                       required
                                       autofocus
                                       placeholder="Ex: Quincaillerie Moderne, Boutique Élégance..."
                                       x-model="shopName"
                                       @input="updateSlug()"
                                       value="{{ old('shop_name') }}"
                                       class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 transition">
                                <x-input-error :messages="$errors->get('shop_name')" class="mt-1" />
                            </div>

                            <!-- Slug / Sous-Domaine Preview -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="slug" class="text-xs font-semibold text-slate-300">
                                        Identifiant web de votre boutique
                                    </label>
                                    <span class="text-[10px] text-slate-500 font-mono">Généré automatiquement</span>
                                </div>

                                <div class="relative flex items-center">
                                    <span class="absolute left-3.5 text-xs font-mono text-slate-500 pointer-events-none">
                                        kamerstock.cm/t/
                                    </span>
                                    <input type="text" 
                                           id="slug" 
                                           name="slug" 
                                           x-model="slug"
                                           @input="checkSlugAvailability()"
                                           value="{{ old('slug') }}"
                                           placeholder="nom-de-boutique"
                                           class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-36 pr-10 py-2.5 text-xs font-mono text-amber-400 placeholder-slate-600 transition">
                                    
                                    <!-- Indicator -->
                                    <div class="absolute right-3">
                                        <template x-if="slugStatus === 'checking'">
                                            <span class="animate-spin text-slate-400 text-xs">◌</span>
                                        </template>
                                        <template x-if="slugStatus === 'available'">
                                            <span class="text-emerald-400 text-xs" title="Disponible">✓</span>
                                        </template>
                                        <template x-if="slugStatus === 'taken'">
                                            <span class="text-rose-400 text-xs" title="Déjà utilisé">✕</span>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center justify-between text-[11px]">
                                    <span :class="slugStatus === 'available' ? 'text-emerald-400' : (slugStatus === 'taken' ? 'text-rose-400' : 'text-slate-500')" 
                                          x-text="slugMessage || 'Identifiant unique de connexion pour vous et vos caissiers.'"></span>
                                </div>
                                <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                            </div>

                            <!-- Type de commerce / Secteur d'activité -->
                            <div>
                                <label for="business_type" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                    Secteur d'activité commercial <span class="text-amber-500">*</span>
                                </label>
                                <select id="business_type" 
                                        name="business_type" 
                                        required
                                        x-model="businessType"
                                        class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-xs text-white transition">
                                    <option value="" disabled selected>Sélectionnez votre type d'activité...</option>
                                    @foreach($businessTypes as $key => $type)
                                        <option value="{{ $key }}" {{ old('business_type') === $key ? 'selected' : '' }}>
                                            {{ $type['label'] ?? ucfirst($key) }} — {{ $type['subtitle'] ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-[11px] text-slate-500 mt-1 block">
                                    Ce choix préconfigurera automatiquement vos familles d'articles, unités de mesure et formulaires.
                                </span>
                                <x-input-error :messages="$errors->get('business_type')" class="mt-1" />
                            </div>

                            <!-- Champ personnalisé si "autre" -->
                            <div x-show="businessType === 'autre'" x-cloak class="pt-1">
                                <label for="business_type_custom" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                    Précisez votre activité
                                </label>
                                <input type="text" 
                                       id="business_type_custom" 
                                       name="business_type_custom" 
                                       value="{{ old('business_type_custom') }}"
                                       placeholder="Ex: Librairie papeterie, Fleuriste..."
                                       class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500">
                            </div>
                        </div>

                        <!-- SECTION 2 : ACCÈS DU GÉRANT -->
                        <div class="space-y-4 pt-4">
                            <div class="text-[11px] font-mono uppercase tracking-wider text-amber-400/90 font-semibold flex items-center gap-2">
                                <span>02. Compte Propriétaire / Gérant</span>
                                <span class="h-px bg-slate-800 flex-1"></span>
                            </div>

                            <!-- Nom Complet -->
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                    Votre nom et prénom <span class="text-amber-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       required
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Marcel Tchamba"
                                       class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 transition">
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                    Adresse e-mail professionnelle <span class="text-amber-500">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required
                                       value="{{ old('email') }}"
                                       placeholder="gérant@moncommerce.com"
                                       class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 transition">
                                <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            </div>

                            <!-- Mot de Passe & Confirmation -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Mot de passe <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="password" 
                                           id="password" 
                                           name="password" 
                                           required
                                           x-model="password"
                                           autocomplete="new-password"
                                           placeholder="Au moins 8 caractères"
                                           class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 transition">
                                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-200 mb-1.5">
                                        Confirmer le mot de passe <span class="text-amber-500">*</span>
                                    </label>
                                    <input type="password" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           required
                                           x-model="passwordConfirmation"
                                           autocomplete="new-password"
                                           placeholder="Répétez le mot de passe"
                                           class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 transition">
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3 : CONDITIONS & VALIDATION -->
                        <div class="pt-4 space-y-4">
                            <!-- Terms checkbox -->
                            <div class="flex items-start gap-3">
                                <input type="checkbox" 
                                       id="terms" 
                                       name="terms" 
                                       required
                                       class="mt-1 w-4 h-4 rounded bg-slate-950 border-white/[0.1] text-amber-500 focus:ring-amber-500">
                                <label for="terms" class="text-xs text-slate-400 font-light leading-relaxed">
                                    J'accepte les conditions d'utilisation et je souhaite démarrer mes <strong class="text-white font-medium">{{ $defaultTrialDays }} jours d'essai gratuit</strong> sans engagement.
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('terms')" class="mt-1" />

                            <!-- Submit Button with loading animation -->
                            <button type="submit" 
                                    :disabled="isSubmitting"
                                    class="w-full py-3.5 px-6 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-xl shadow-amber-500/20 transition duration-200 flex items-center justify-center gap-2 font-outfit disabled:opacity-75 disabled:cursor-wait">
                                <template x-if="!isSubmitting">
                                    <span class="flex items-center gap-2">
                                        <span>Créer et déployer ma boutique</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Initialisation de votre base de données...</span>
                                    </span>
                                </template>
                            </button>
                        </div>

                    </form>

                </div>

                <!-- Right Column : Reassurance, Features & Social Proof (Linear Style) -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-28">
                    
                    <!-- Card 1 : What's included -->
                    <div class="bg-slate-900/40 border border-white/[0.08] rounded-2xl p-6 sm:p-7 backdrop-blur-md space-y-5">
                        <div class="flex items-center justify-between border-b border-white/[0.06] pb-4">
                            <span class="text-xs font-semibold text-white font-outfit">Inclus dans votre essai gratuit</span>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">14 JOURS OFFERTS</span>
                        </div>

                        <div class="space-y-4 text-xs font-light text-slate-300">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <strong class="text-white font-medium block">Base de données 100% isolée</strong>
                                    <span class="text-slate-400 text-[11px]">Vos ventes, marges et clients sont stockés dans un conteneur MySQL dédié et étanche.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <strong class="text-white font-medium block">Caisse POS & Reçus thermiques</strong>
                                    <span class="text-slate-400 text-[11px]">Encaissez vos clients en quelques clics avec gestion du fond de caisse et clôtures journalières.</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <strong class="text-white font-medium block">Gestion des crédits & alertes stock</strong>
                                    <span class="text-slate-400 text-[11px]">Plafonds autorisés par client, suivi des échéances et notifications de réapprovisionnement.</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/[0.06] text-[11px] text-slate-500 font-mono flex items-center justify-between">
                            <span>Devise par défaut : FCFA</span>
                            <span>Fuseau : Africa/Douala</span>
                        </div>
                    </div>

                    <!-- Card 2 : Reassurance quote -->
                    <div class="bg-gradient-to-br from-slate-900/60 to-slate-950 border border-white/[0.06] rounded-2xl p-6 text-xs space-y-3">
                        <div class="flex items-center gap-1 text-amber-400 text-sm">
                            ★★★★★
                        </div>
                        <p class="text-slate-300 italic font-light leading-relaxed">
                            « L'isolation des stocks et la rapidité du point de vente nous ont permis d'éliminer les erreurs de caisse dès la première semaine. »
                        </p>
                        <div class="flex items-center gap-3 pt-2">
                            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-amber-400 text-xs font-outfit">
                                QM
                            </div>
                            <div>
                                <span class="text-white font-semibold block text-xs">Quincaillerie Moderne</span>
                                <span class="text-slate-500 text-[10px]">Douala, Cameroun</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 border-t border-white/[0.06] text-[11px] text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span>&copy; {{ date('Y') }} KamerStock SaaS. Tous droits réservés.</span>
            <div class="flex items-center gap-4">
                <a href="/" class="hover:text-slate-300 transition">Accueil</a>
                <a href="{{ route('login') }}" class="hover:text-slate-300 transition">Connexion gérant</a>
                <a href="{{ route('landlord.login') }}" class="hover:text-amber-400 transition">Console Landlord</a>
            </div>
        </footer>

    </div>

    <!-- Alpine.js Onboarding Logic -->
    <script>
        function onboardingForm() {
            return {
                shopName: '{{ old('shop_name', '') }}',
                slug: '{{ old('slug', '') }}',
                businessType: '{{ old('business_type', '') }}',
                password: '',
                passwordConfirmation: '',
                slugStatus: 'idle', // idle, checking, available, taken
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
                        .replace(/[^\w\s-]/g, '')
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
                    this.slugMessage = 'Vérification de la disponibilité...';

                    this.slugTimer = setTimeout(() => {
                        fetch(`{{ route('check.slug') }}?slug=${encodeURIComponent(this.slug)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.available) {
                                    this.slugStatus = 'available';
                                    this.slugMessage = data.message;
                                } else {
                                    this.slugStatus = 'taken';
                                    this.slugMessage = data.message;
                                }
                            })
                            .catch(() => {
                                this.slugStatus = 'idle';
                                this.slugMessage = '';
                            });
                    }, 400);
                }
            }
        }
    </script>
</body>
</html>
