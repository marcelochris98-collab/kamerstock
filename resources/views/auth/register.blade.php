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

        /* 2-column input grid for compact zero-scroll height */
        .field-pair {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.625rem;
        }
        @media (min-width: 480px) {
            .field-pair {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="min-h-screen lg:h-screen lg:overflow-hidden bg-slate-950 text-slate-100 antialiased selection:bg-amber-500 selection:text-slate-950 relative flex flex-col justify-between"
      x-data="onboardingForm()">

    <!-- Ambient Grid & Glow -->
    <div class="fixed inset-0 linear-grid pointer-events-none z-0"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[700px] h-[250px] bg-gradient-to-b from-amber-500/10 via-amber-500/5 to-transparent blur-3xl pointer-events-none z-0"></div>

    <!-- ========================================================================= -->
    <!-- 1. COMPACT TOP HEADER -->
    <!-- ========================================================================= -->
    <header class="relative z-10 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 h-12 shrink-0 flex items-center justify-between border-b border-white/[0.04]">
        <a href="/" class="flex items-center gap-2 group">
            <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center font-black text-slate-950 font-outfit text-xs tracking-tight shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform duration-200">
                K
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs sm:text-sm font-bold text-white tracking-tight font-outfit">KamerStock</span>
                <span class="px-1.5 py-0.2 text-[8px] font-semibold text-amber-400 bg-amber-500/10 border border-amber-500/20 rounded">SaaS</span>
            </div>
        </a>

        <div class="text-[11px] text-slate-400">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-semibold text-amber-400 hover:text-amber-300 ml-1 transition">
                Connexion gérant &rarr;
            </a>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 2. MAIN CONTENT (ZERO-SCROLL COMPACT 2-COLUMN VIEWPORT) -->
    <!-- ========================================================================= -->
    <main class="relative z-10 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-3 flex-1 flex items-center justify-center">
        <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-center">
            
            <!-- LEFT COLUMN : FIXED FITTED ONBOARDING FORM (Everything visible at once) -->
            <div class="lg:col-span-7 bg-slate-900/80 border border-white/[0.08] backdrop-blur-xl rounded-2xl p-4 sm:p-5 lg:p-6 shadow-2xl shadow-black/60">
                
                <!-- Compact Header -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            14 jours d'essai gratuit · Sans carte bancaire
                        </div>
                        <h1 class="text-lg sm:text-xl font-black text-white font-outfit tracking-tight">
                            Lancez votre boutique en 2 minutes
                        </h1>
                    </div>

                    <span class="text-[9px] text-slate-500 font-mono hidden sm:inline shrink-0 pt-1">
                        Étape unique
                    </span>
                </div>

                @if ($errors->any())
                    <div class="mb-2.5 p-2 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-400 text-[10px]">
                        <div class="font-bold mb-0.5">Veuillez vérifier les champs indiqués :</div>
                        <ul class="list-disc pl-4 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-2.5" @submit="isSubmitting = true">
                    @csrf

                    <!-- ROW 1 : Nom de Boutique + Secteur d'Activité -->
                    <div class="field-pair">
                        <!-- Nom Boutique -->
                        <div>
                            <label for="shop_name" class="block text-[11px] font-semibold text-slate-200 mb-0.5">
                                Nom du commerce ou boutique <span class="text-amber-500">*</span>
                            </label>
                            <input type="text" 
                                   id="shop_name" 
                                   name="shop_name" 
                                   required
                                   autofocus
                                   placeholder="Ex: Quincaillerie Centrale..."
                                   x-model="shopName"
                                   @input="updateSlug()"
                                   value="{{ old('shop_name') }}"
                                   class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 transition">
                            <x-input-error :messages="$errors->get('shop_name')" class="mt-0.5 text-[10px]" />
                        </div>

                        <!-- Secteur d'activité -->
                        <div>
                            <label for="business_type" class="block text-[11px] font-semibold text-slate-200 mb-0.5">
                                Secteur d'activité <span class="text-amber-500">*</span>
                            </label>
                            <select id="business_type" 
                                    name="business_type" 
                                    required
                                    x-model="businessType"
                                    class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-xs text-white transition">
                                <option value="" disabled selected>Choisir un secteur...</option>
                                @foreach($businessTypes as $key => $type)
                                    <option value="{{ $key }}" {{ old('business_type') === $key ? 'selected' : '' }}>
                                        {{ $type['label'] ?? ucfirst($key) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('business_type')" class="mt-0.5 text-[10px]" />
                        </div>
                    </div>

                    <!-- Champ dynamique si "autre" -->
                    <div x-show="businessType === 'autre'" x-cloak>
                        <input type="text" 
                               name="business_type_custom" 
                               value="{{ old('business_type_custom') }}"
                               placeholder="Précisez votre activité (ex: Librairie, Fleuriste...)"
                               class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 rounded-lg px-3 py-1 text-xs text-white placeholder-slate-500">
                    </div>

                    <!-- ROW 2 : Identifiant Web / Slug (Inline Preview) -->
                    <div>
                        <div class="flex items-center justify-between mb-0.5">
                            <label for="slug" class="text-[11px] font-semibold text-slate-300">
                                Adresse web dédiée de votre boutique
                            </label>
                            <!-- Live status indicator -->
                            <div class="text-[10px] font-mono flex items-center gap-1">
                                <template x-if="slugStatus === 'checking'">
                                    <span class="text-slate-400">◌ Vérification...</span>
                                </template>
                                <template x-if="slugStatus === 'available'">
                                    <span class="text-emerald-400 font-semibold">✓ Disponible</span>
                                </template>
                                <template x-if="slugStatus === 'taken'">
                                    <span class="text-rose-400 font-semibold" x-text="slugMessage"></span>
                                </template>
                            </div>
                        </div>

                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-[11px] font-mono text-slate-500 pointer-events-none select-none">
                                kamerstock.cm/t/
                            </span>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   x-model="slug"
                                   @input="checkSlugAvailability()"
                                   value="{{ old('slug') }}"
                                   placeholder="identifiant-boutique"
                                   class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg pl-32 pr-3 py-1.5 text-xs font-mono text-amber-400 placeholder-slate-600 transition">
                        </div>
                        <x-input-error :messages="$errors->get('slug')" class="mt-0.5 text-[10px]" />
                    </div>

                    <!-- ROW 3 : Nom Gérant + Email Professionnel -->
                    <div class="field-pair">
                        <div>
                            <label for="name" class="block text-[11px] font-semibold text-slate-200 mb-0.5">
                                Votre nom complet <span class="text-amber-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   required
                                   value="{{ old('name') }}"
                                   placeholder="Ex: Marcel Tchamba"
                                   class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 transition">
                            <x-input-error :messages="$errors->get('name')" class="mt-0.5 text-[10px]" />
                        </div>

                        <div>
                            <label for="email" class="block text-[11px] font-semibold text-slate-200 mb-0.5">
                                Adresse e-mail professionnelle <span class="text-amber-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   required
                                   value="{{ old('email') }}"
                                   placeholder="gerant@commerce.com"
                                   class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 transition">
                            <x-input-error :messages="$errors->get('email')" class="mt-0.5 text-[10px]" />
                        </div>
                    </div>

                    <!-- ROW 4 : Mot de passe + Confirmation -->
                    <div class="field-pair">
                        <div>
                            <label for="password" class="block text-[11px] font-semibold text-slate-200 mb-0.5">
                                Mot de passe <span class="text-amber-500">*</span>
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   required
                                   x-model="password"
                                   autocomplete="new-password"
                                   placeholder="8 caractères min."
                                   class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 transition">
                            <x-input-error :messages="$errors->get('password')" class="mt-0.5 text-[10px]" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-semibold text-slate-200 mb-0.5">
                                Confirmer mot de passe <span class="text-amber-500">*</span>
                            </label>
                            <input type="password" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   x-model="passwordConfirmation"
                                   autocomplete="new-password"
                                   placeholder="Répétez le mot de passe"
                                   class="w-full bg-slate-950/80 border border-white/[0.08] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 transition">
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-0.5 text-[10px]" />
                        </div>
                    </div>

                    <!-- ROW 5 : Conditions + Bouton de soumission -->
                    <div class="pt-1 space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" 
                                   id="terms" 
                                   name="terms" 
                                   required
                                   checked
                                   class="w-3.5 h-3.5 rounded bg-slate-950 border-white/[0.1] text-amber-500 focus:ring-amber-500">
                            <label for="terms" class="text-[10px] sm:text-[11px] text-slate-400 font-light select-none">
                                J'accepte les conditions et lance mes <span class="text-amber-400 font-medium">14 jours d'essai gratuit</span>.
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('terms')" class="text-[10px]" />

                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-amber-400 via-amber-300 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-md shadow-amber-500/20 transition duration-150 flex items-center justify-center gap-2 font-outfit disabled:opacity-75 disabled:cursor-wait">
                            <template x-if="!isSubmitting">
                                <span class="flex items-center gap-2">
                                    <span>Lancer ma boutique gratuitement &rarr;</span>
                                </span>
                            </template>
                            <template x-if="isSubmitting">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-3.5 w-3.5 text-slate-950" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Déploiement de votre base de données...</span>
                                </span>
                            </template>
                        </button>
                    </div>

                </form>

            </div>

            <!-- RIGHT COLUMN : COMPACT REASSURANCE & PRODUCT PILLARS (Matches Left Height) -->
            <div class="lg:col-span-5 space-y-3 hidden lg:flex flex-col justify-between">
                
                <!-- Perks Box -->
                <div class="bg-slate-900/40 border border-white/[0.08] rounded-2xl p-4 sm:p-5 backdrop-blur-md space-y-3">
                    <div class="flex items-center justify-between border-b border-white/[0.06] pb-2.5">
                        <span class="text-xs font-semibold text-white font-outfit">Avantages de votre espace dédié</span>
                        <span class="text-[8px] font-mono font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">ÉTANCHE & ISOLÉ</span>
                    </div>

                    <div class="space-y-3 text-xs font-light text-slate-300">
                        <div class="flex items-start gap-2.5">
                            <div class="w-5 h-5 rounded-md bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold">
                                01
                            </div>
                            <div>
                                <strong class="text-white font-medium block text-xs">Base de données MySQL dédiée</strong>
                                <span class="text-slate-400 text-[11px] leading-relaxed">Vos stocks, prix et marges sont strictement confidentiels et physiquement isolés.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div class="w-5 h-5 rounded-md bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold">
                                02
                            </div>
                            <div>
                                <strong class="text-white font-medium block text-xs">Caisse POS & Reçus thermiques</strong>
                                <span class="text-slate-400 text-[11px] leading-relaxed">Encaissez vos clients en moins de 3 secondes avec contrôle strict du fond de caisse.</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-2.5">
                            <div class="w-5 h-5 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-bold">
                                03
                            </div>
                            <div>
                                <strong class="text-white font-medium block text-xs">Crédits clients & Relances</strong>
                                <span class="text-slate-400 text-[11px] leading-relaxed">Plafonds personnalisés par client et blocage automatique en cas d'encours dépassé.</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-white/[0.06] text-[10px] text-slate-500 font-mono flex items-center justify-between">
                        <span>Devise : FCFA</span>
                        <span>Multi-Boutiques : Inclus</span>
                    </div>
                </div>

                <!-- Small Trust Quote -->
                <div class="bg-slate-900/30 border border-white/[0.05] rounded-xl p-3 text-[11px] flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs shrink-0 font-outfit">
                        ★
                    </div>
                    <div class="text-slate-400 leading-tight text-[11px]">
                        <span class="text-slate-200 font-medium block">Déploiement instantané garanti</span>
                        Votre environnement est prêt en moins de 10 secondes.
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- 3. COMPACT FOOTER -->
    <!-- ========================================================================= -->
    <footer class="relative z-10 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 h-8 shrink-0 border-t border-white/[0.04] text-[10px] text-slate-500 flex items-center justify-between">
        <span>&copy; {{ date('Y') }} KamerStock SaaS. Précision, isolation et performance commerciale.</span>
        <div class="flex items-center gap-4">
            <a href="/" class="hover:text-slate-300 transition">Accueil</a>
            <a href="{{ route('login') }}" class="hover:text-slate-300 transition">Connexion</a>
            <a href="{{ route('landlord.login') }}" class="hover:text-amber-400 transition">Landlord</a>
        </div>
    </footer>

    <!-- Alpine.js Onboarding Logic -->
    <script>
        function onboardingForm() {
            return {
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

                    this.slugTimer = setTimeout(() => {
                        fetch(`{{ route('check.slug') }}?slug=${encodeURIComponent(this.slug)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.available) {
                                    this.slugStatus = 'available';
                                    this.slugMessage = 'Disponible';
                                } else {
                                    this.slugStatus = 'taken';
                                    this.slugMessage = data.message;
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
