<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamerStock — Le système de gestion commerciale pour les boutiques modernes</title>
    <meta name="description" content="Plateforme SaaS nouvelle génération de gestion de stock, caisse POS, suivi des crédits et architecture multi-boutiques étanche. Conçue pour la vitesse et le contrôle absolu.">
    
    <!-- Script Anti-Flicker Thème Sombre / Clair persistant -->
    <script>
        if (localStorage.getItem('kamerstock_theme') === 'light' || (!('kamerstock_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- CSS & JS Assets (Tailwind & Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            letter-spacing: -0.011em;
        }
        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }
        [x-cloak] { display: none !important; }

        /* Subtle Linear-style grid */
        .linear-grid {
            background-size: 60px 60px;
            background-image: 
                linear-gradient(to right, rgba(0, 0, 0, 0.035) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, 0.035) 1px, transparent 1px);
        }
        .dark .linear-grid {
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.025) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
        }

        /* Ambient Glows */
        .ambient-glow-top {
            background: radial-gradient(ellipse 70% 40% at 50% -10%, rgba(245, 158, 11, 0.08), transparent 70%);
        }
        .dark .ambient-glow-top {
            background: radial-gradient(ellipse 70% 40% at 50% -10%, rgba(245, 158, 11, 0.16), transparent 70%);
        }

        .ambient-glow-bottom {
            background: radial-gradient(circle 500px at 50% 100%, rgba(245, 158, 11, 0.04), transparent 70%);
        }
        .dark .ambient-glow-bottom {
            background: radial-gradient(circle 500px at 50% 100%, rgba(245, 158, 11, 0.08), transparent 70%);
        }

        /* Glass hair-line borders */
        .linear-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(16px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .dark .linear-card {
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.07);
            box-shadow: none;
        }
        .linear-card:hover {
            border-color: rgba(245, 158, 11, 0.4);
        }
        .dark .linear-card:hover {
            background: rgba(15, 23, 42, 0.65);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-950 dark:text-slate-100 antialiased overflow-x-hidden selection:bg-amber-500 selection:text-slate-950 transition-colors duration-200" 
      x-data="{ 
          darkMode: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              this.darkMode = !this.darkMode;
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('kamerstock_theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('kamerstock_theme', 'light');
              }
          },
          mobileMenuOpen: false, 
          activeFaq: 1, 
          pricingBilling: 'monthly'
      }">

    <!-- Ambient backdrop decorations -->
    <div class="fixed inset-0 linear-grid pointer-events-none z-0"></div>
    <div class="fixed top-0 left-0 right-0 h-[650px] ambient-glow-top pointer-events-none z-0"></div>
    <div class="fixed bottom-0 left-0 right-0 h-[450px] ambient-glow-bottom pointer-events-none z-0"></div>

    <!-- ========================================================================= -->
    <!-- 1. HEADER / NAVBAR (Linear Style) -->
    <!-- ========================================================================= -->
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/80 dark:border-white/[0.06] dark:bg-slate-950/70 backdrop-blur-xl transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center font-black text-slate-950 font-outfit text-base tracking-tight shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform duration-200">
                    K
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight font-outfit">KamerStock</span>
                    <span class="px-1.5 py-0.5 text-[9px] font-semibold text-amber-600 dark:text-amber-400/90 bg-amber-500/10 border border-amber-500/20 rounded">SaaS 2.0</span>
                </div>
            </a>

            <!-- Central Nav Links (Linear Minimalist Style) -->
            <nav class="hidden md:flex items-center gap-7 text-xs font-medium text-slate-600 dark:text-slate-400">
                <a href="#features" class="hover:text-slate-900 dark:hover:text-white transition-colors duration-200">Fonctionnalités</a>
                <a href="#multitenant" class="hover:text-slate-900 dark:hover:text-white transition-colors duration-200">Multi-Boutiques</a>
                <a href="#security" class="hover:text-slate-900 dark:hover:text-white transition-colors duration-200">Sécurité & Isolation</a>
                <a href="#pricing" class="hover:text-slate-900 dark:hover:text-white transition-colors duration-200">Tarifs</a>
                <a href="#faq" class="hover:text-slate-900 dark:hover:text-white transition-colors duration-200">FAQ</a>
            </nav>

            <!-- Actions Right -->
            <div class="hidden sm:flex items-center gap-3">
                
                <!-- Bouton Bascule Mode Sombre / Mode Clair -->
                <button type="button" 
                        @click="toggleTheme()"
                        class="p-2 rounded-xl transition-all duration-200 border cursor-pointer focus:outline-none"
                        :class="darkMode 
                            ? 'bg-white/[0.04] border-white/[0.08] text-amber-400 hover:bg-white/[0.08]' 
                            : 'bg-slate-100 border-slate-200 text-slate-700 hover:bg-slate-200 hover:text-slate-900'"
                        :title="darkMode ? 'Passer en mode clair' : 'Passer en mode sombre'"
                        aria-label="Changer le thème">
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

                <a href="{{ route('landlord.login') }}" 
                   class="text-xs font-medium text-slate-600 hover:text-amber-500 dark:text-slate-400 dark:hover:text-amber-400 transition-colors px-2.5 py-1.5">
                    Console Landlord
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="px-4 py-1.5 text-xs font-semibold text-slate-950 bg-amber-500 hover:bg-amber-400 rounded-lg transition-all duration-200 shadow-sm shadow-amber-500/20 font-outfit">
                        Mon Tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="text-xs font-medium text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white transition-colors px-2.5 py-1.5">
                        Se connecter
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-slate-950 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 rounded-lg transition-all duration-200 shadow-md shadow-amber-500/20 font-outfit">
                            <span>Créer ma boutique</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <div class="flex items-center gap-2 sm:hidden">
                <button type="button" 
                        @click="toggleTheme()"
                        class="p-2 rounded-lg border text-xs"
                        :class="darkMode ? 'bg-white/[0.04] border-white/[0.08] text-amber-400' : 'bg-slate-100 border-slate-200 text-slate-700'">
                    <span x-show="darkMode">☀️</span>
                    <span x-show="!darkMode">🌙</span>
                </button>

                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        type="button"
                        class="text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white p-2 rounded-lg bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 focus:outline-none transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-cloak/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-b border-slate-200 dark:border-white/[0.08] bg-white/95 dark:bg-slate-950/95 backdrop-blur-2xl px-6 py-5 space-y-4 shadow-2xl" 
             x-cloak>
            <nav class="flex flex-col gap-3 text-xs font-medium text-slate-700 dark:text-slate-300">
                <a @click="mobileMenuOpen = false" href="#features" class="py-1.5 hover:text-amber-500 transition">Fonctionnalités</a>
                <a @click="mobileMenuOpen = false" href="#multitenant" class="py-1.5 hover:text-amber-500 transition">Architecture Multi-Boutiques</a>
                <a @click="mobileMenuOpen = false" href="#security" class="py-1.5 hover:text-amber-500 transition">Sécurité & Isolation</a>
                <a @click="mobileMenuOpen = false" href="#pricing" class="py-1.5 hover:text-amber-500 transition">Plans & Tarifs</a>
                <a @click="mobileMenuOpen = false" href="#faq" class="py-1.5 hover:text-amber-500 transition">FAQ</a>
            </nav>
            <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80 flex flex-col gap-2.5">
                <a href="{{ route('login') }}" class="w-full text-center py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg">
                    Connexion Boutique
                </a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="w-full text-center py-2 text-xs font-bold text-slate-950 bg-amber-500 rounded-lg shadow-md shadow-amber-500/20">
                        Créer ma boutique gratuitement
                    </a>
                @endif
                <a href="{{ route('landlord.login') }}" class="w-full text-center py-2 text-[11px] font-medium text-slate-500 dark:text-slate-400 hover:text-amber-500">
                    Console SuperAdmin Landlord &rarr;
                </a>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="relative z-40 max-w-4xl mx-auto px-4 mt-4">
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 dark:text-emerald-400 text-xs px-4 py-2.5 rounded-lg flex items-center justify-between shadow-lg backdrop-blur-md">
                <span class="font-medium">{{ session('success') }}</span>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500/70 hover:text-emerald-400">✕</button>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- 2. HERO SECTION (Linear Inspired: Clean, Razor-Sharp, Spacious) -->
    <!-- ========================================================================= -->
    <section class="relative z-10 pt-16 pb-20 md:pt-24 md:pb-32 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto text-center">
        
        <!-- Subtle Pill Announcement -->
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/70 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] hover:border-amber-500/30 rounded-full text-[11px] font-medium text-slate-700 dark:text-slate-300 mb-8 transition-colors group cursor-pointer shadow-sm">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
            <span>KamerStock SaaS 2.0</span>
            <span class="text-slate-400 dark:text-slate-500">·</span>
            <span class="text-slate-600 dark:text-slate-400 group-hover:text-amber-500 dark:group-hover:text-amber-400 transition-colors">Architecture Multi-Boutiques Étanche</span>
            <svg class="w-3 h-3 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>

        <!-- Grand Headline (H1) -->
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white font-outfit leading-[1.08] max-w-4xl mx-auto">
            Le système d'exploitation commercial des <span class="bg-gradient-to-r from-amber-500 via-amber-400 to-amber-600 bg-clip-text text-transparent">boutiques modernes</span>.
        </h1>

        <!-- Subtitle -->
        <p class="mt-6 text-sm sm:text-base md:text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto font-light leading-relaxed">
            Gestion des stocks millimétrée, caisse POS instantanée, suivi rigoureux des crédits clients et isolation totale des données par boutique.
        </p>

        <!-- CTA Row -->
        <div class="mt-9 flex flex-col sm:flex-row items-center justify-center gap-3.5">
            @auth
                <a href="{{ route('dashboard') }}" 
                   class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 text-xs font-bold rounded-xl transition shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 font-outfit">
                    <span>Accéder à mon tableau de bord</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            @else
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" 
                       class="w-full sm:w-auto px-7 py-3 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 text-xs font-bold rounded-xl transition shadow-lg shadow-amber-500/25 flex items-center justify-center gap-2 font-outfit">
                        <span>Démarrer l'essai gratuit</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @endif
                <a href="#features" 
                   class="w-full sm:w-auto px-6 py-3 bg-white hover:bg-slate-100 text-slate-800 dark:bg-white/[0.03] dark:hover:bg-white/[0.06] dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-white/[0.15] transition flex items-center justify-center gap-2 shadow-sm">
                    <span>Explorer les modules</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
            @endauth
        </div>

        <p class="mt-4 text-[11px] text-slate-500 font-medium">
            14 jours d'essai sans engagement · Aucune carte bancaire requise · Déploiement en 2 minutes
        </p>

        <!-- ========================================================================= -->
        <!-- 3. HERO SHOWCASE : Sleek Linear-style App Canvas -->
        <!-- ========================================================================= -->
        <div class="mt-14 relative mx-auto max-w-5xl">
            <!-- Ambient Behind-frame Glow -->
            <div class="absolute -inset-1 bg-gradient-to-r from-amber-500/20 via-indigo-500/10 to-amber-500/20 rounded-2xl blur-2xl opacity-50 -z-10"></div>

            <div class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-slate-900/80 shadow-2xl shadow-slate-200/50 dark:shadow-black/80 backdrop-blur-xl overflow-hidden text-left">
                
                <!-- Window Chrome / Top Bar -->
                <div class="h-10 border-b border-slate-200 dark:border-white/[0.06] bg-slate-100/70 dark:bg-slate-950/60 px-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                        <span class="ml-3 text-[11px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1.5">
                            <span class="text-slate-400 dark:text-slate-600">app.kamerstock.cm</span>
                            <span class="text-slate-400 dark:text-slate-600">/</span>
                            <span class="text-amber-600 dark:text-amber-400 font-medium">boutique-principale</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span>
                            BDD Dédiée Active
                        </span>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono hidden sm:inline">Ctrl + K</span>
                    </div>
                </div>

                <!-- Canvas Preview Content -->
                <div class="p-5 sm:p-6 space-y-5 bg-gradient-to-b from-slate-50 to-white dark:from-slate-900/50 dark:to-slate-950/70">
                    
                    <!-- KPI Row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                        <div class="p-3.5 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-slate-900/60 shadow-sm">
                            <span class="text-[10px] uppercase font-mono text-slate-500">Chiffre d'Affaires Jour</span>
                            <div class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-outfit mt-1">1 240 500 <span class="text-[10px] font-normal font-mono text-amber-500">FCFA</span></div>
                            <span class="text-[10px] text-emerald-500 dark:text-emerald-400 font-medium">+18.4% vs hier</span>
                        </div>
                        <div class="p-3.5 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-slate-900/60 shadow-sm">
                            <span class="text-[10px] uppercase font-mono text-slate-500">Tickets de Caisse</span>
                            <div class="text-base sm:text-lg font-bold text-slate-900 dark:text-white font-outfit mt-1">142 <span class="text-[10px] font-normal text-slate-400">ventes</span></div>
                            <span class="text-[10px] text-slate-500">Panier moy: 8 730 FCFA</span>
                        </div>
                        <div class="p-3.5 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-slate-900/60 shadow-sm">
                            <span class="text-[10px] uppercase font-mono text-slate-500">Alertes Stock</span>
                            <div class="text-base sm:text-lg font-bold text-rose-500 dark:text-rose-400 font-outfit mt-1">3 <span class="text-[10px] font-normal text-slate-400">ruptures</span></div>
                            <span class="text-[10px] text-rose-500">Réapprovisionner</span>
                        </div>
                        <div class="p-3.5 rounded-lg border border-slate-200 dark:border-white/[0.05] bg-white dark:bg-slate-900/60 shadow-sm">
                            <span class="text-[10px] uppercase font-mono text-slate-500">Encours Crédits</span>
                            <div class="text-base sm:text-lg font-bold text-amber-600 dark:text-amber-400 font-outfit mt-1">480 000 <span class="text-[10px] font-normal font-mono text-slate-400">FCFA</span></div>
                            <span class="text-[10px] text-slate-500">5 clients relançables</span>
                        </div>
                    </div>

                    <!-- Visual POS / Stock Mock Table -->
                    <div class="rounded-lg border border-slate-200 dark:border-white/[0.05] bg-slate-50/50 dark:bg-slate-950/50 overflow-hidden">
                        <div class="px-4 py-2 border-b border-slate-200 dark:border-white/[0.05] flex items-center justify-between text-[11px] font-mono text-slate-500">
                            <span>DERNIÈRES OPÉRATIONS DE CAISSE EN TEMPS RÉEL</span>
                            <span class="text-emerald-500 dark:text-emerald-400">● Caisse Ouverte · Fond: 50 000 FCFA</span>
                        </div>
                        <div class="divide-y divide-slate-200 dark:divide-white/[0.03] text-xs">
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-mono text-slate-400">#TK-1084</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Sac Ciment Dangote 50kg (x10)</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-mono text-slate-800 dark:text-slate-200">52 000 FCFA</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Espèces</span>
                                </div>
                            </div>
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-mono text-slate-400">#TK-1083</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Fer à Béton Ø12 (x25)</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-mono text-slate-800 dark:text-slate-200">112 500 FCFA</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400">Orange Money</span>
                                </div>
                            </div>
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-mono text-slate-400">#TK-1082</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">Tuyau PVC Pression Ø50 (x6)</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="font-mono text-slate-800 dark:text-slate-200">19 800 FCFA</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">Crédit Client</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- 4. TRUSTED SECTORS TICKER (Linear Minimalist Strip) -->
    <!-- ========================================================================= -->
    <div class="border-y border-slate-200/80 bg-white/60 dark:border-white/[0.06] dark:bg-slate-950/40 py-6 transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 mb-3">
                Solution éprouvée sur le terrain pour les commerces exigeants
            </p>
            <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-xs font-medium text-slate-600 dark:text-slate-400">
                <span class="hover:text-slate-900 dark:hover:text-white transition-colors">Quincailleries & BTP</span>
                <span class="text-slate-300 dark:text-slate-700">·</span>
                <span class="hover:text-slate-900 dark:hover:text-white transition-colors">Prêt-à-porter & Maroquinerie</span>
                <span class="text-slate-300 dark:text-slate-700">·</span>
                <span class="hover:text-slate-900 dark:hover:text-white transition-colors">Supérettes & Alimentation</span>
                <span class="text-slate-300 dark:text-slate-700">·</span>
                <span class="hover:text-slate-900 dark:hover:text-white transition-colors">High-Tech & Électronique</span>
                <span class="text-slate-300 dark:text-slate-700">·</span>
                <span class="hover:text-slate-900 dark:hover:text-white transition-colors">Réseaux Franchisés</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. BENTO GRID FEATURES (Linear's Signature Layout) -->
    <!-- ========================================================================= -->
    <section id="features" class="py-24 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="max-w-3xl mb-16">
            <span class="text-xs font-semibold text-amber-500 dark:text-amber-400 tracking-wider uppercase font-mono block mb-2">Modules Métier</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white font-outfit tracking-tight leading-tight">
                Tout ce dont votre commerce a besoin.<br>Rien de superflu.
            </h2>
            <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                Une interface épurée pensée pour les gérants et les caissiers : rapidité de saisie, visibilité complète sur la trésorerie et élimination des pertes.
            </p>
        </div>

        <!-- Bento Grid Container -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            
            <!-- Bento 1 : Caisse POS (Wide 2-col) -->
            <div class="md:col-span-2 linear-card rounded-2xl p-7 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-500 dark:text-amber-400 flex items-center justify-center mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white font-outfit">Point de Vente (POS) & Clôtures Instantanées</h3>
                    <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed max-w-xl">
                        Encaissez en quelques secondes par code-barres ou raccourci clavier. Génération automatique de reçus PDF, tickets thermiques et contrôle strict des écarts de fond de caisse à chaque clôture de journée.
                    </p>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-mono">
                    <span class="text-amber-600 dark:text-amber-400">Paiements mixtes (Espèces, Mobile Money)</span>
                    <span class="hidden sm:inline text-slate-400 dark:text-slate-500">&lt; 3s par ticket</span>
                </div>
            </div>

            <!-- Bento 2 : Stocks & Alertes (1-col) -->
            <div class="linear-card rounded-2xl p-7 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 dark:text-indigo-400 flex items-center justify-center mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white font-outfit">Stocks & Ruptures</h3>
                    <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                        Traçabilité rigoureuse des entrées, sorties et casses. Alertes visuelles dès qu'un article franchit son seuil de sécurité.
                    </p>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-white/[0.06] text-xs text-indigo-600 dark:text-indigo-400 font-mono">
                    Valorisation temps réel (PMP)
                </div>
            </div>

            <!-- Bento 3 : Crédits Clients (1-col) -->
            <div class="linear-card rounded-2xl p-7 flex flex-col justify-between">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-500 dark:text-amber-400 flex items-center justify-center mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white font-outfit">Crédits Clients & Recouvrement</h3>
                    <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                        Fixez des plafonds autorisés par client, suivez les échéances et enregistrez les règlements partiels avec historique infalsifiable.
                    </p>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-white/[0.06] text-xs text-amber-600 dark:text-amber-400 font-mono">
                    Score de solvabilité automatique
                </div>
            </div>

            <!-- Bento 4 : Multi-Boutiques (Wide 2-col) -->
            <div class="md:col-span-2 linear-card rounded-2xl p-7 flex flex-col justify-between" id="multitenant">
                <div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 dark:text-emerald-400 flex items-center justify-center mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white font-outfit">Multi-Boutiques & Succursales Isolées</h3>
                    <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed max-w-xl">
                        Pilotez plusieurs succursales ou filiales sous une même enseigne. Chaque magasin possède sa propre base de données, son catalogue et ses caissiers, tandis que la direction supervise les performances globales.
                    </p>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 font-mono">
                    <span class="text-emerald-600 dark:text-emerald-400">Architecture Database-per-tenant</span>
                    <span class="hidden sm:inline text-slate-400 dark:text-slate-500">Zéro fuite de données inter-magasins</span>
                </div>
            </div>

        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- 6. BUILT FOR SPEED & CONTROL (Linear 3-Pillar Section) -->
    <!-- ========================================================================= -->
    <section class="py-20 border-t border-slate-200/80 bg-slate-100/50 dark:border-white/[0.06] dark:bg-slate-900/20 relative z-10 transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                
                <div class="space-y-3">
                    <div class="text-amber-500 dark:text-amber-400 font-mono text-xs font-semibold">01 / Vitesse & Raccourcis</div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white font-outfit">Naviguez à la vitesse de la pensée</h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                        Recherche universelle instantanée, scan direct par douchette code-barres et bascule de tarification en 1 clic sans recharger la page.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="text-amber-500 dark:text-amber-400 font-mono text-xs font-semibold">02 / Résilience Métier</div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white font-outfit">Protection en Lecture Seule</h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                        Si un forfait expire, vos caissiers peuvent toujours consulter les prix et les stocks historiques. Pas de coupure brutale au comptoir.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="text-amber-500 dark:text-amber-400 font-mono text-xs font-semibold">03 / Propriété des Données</div>
                    <h4 class="text-base font-bold text-slate-900 dark:text-white font-outfit">Sauvegardes SQL Isolées</h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                        Vos données ne sont jamais prisonnières. Snapshots automatiques réguliers et téléchargement de vos bases de données en 1 clic.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 7. SECURITY & ARCHITECTURE HIGHLIGHT (Clean & Authoritative) -->
    <!-- ========================================================================= -->
    <section id="security" class="py-24 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="linear-card rounded-3xl p-8 sm:p-12 border border-slate-200 dark:border-white/[0.08] relative overflow-hidden">
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-4">
                    <span class="text-xs font-semibold text-emerald-500 dark:text-emerald-400 font-mono uppercase tracking-wider">Sécurité d'Entreprise</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-outfit tracking-tight">
                        Vos chiffres d'affaires et clients protégés comme dans un coffre-fort.
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                        Contrairement aux SaaS basiques qui stockent tous les clients dans la même table, KamerStock alloue une base de données physique distincte pour chaque entreprise. Un concurrent ne pourra jamais intercepter vos données.
                    </p>

                    <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-slate-700 dark:text-slate-300">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-500 dark:text-emerald-400 font-bold">✓</span>
                            <span>Bases MySQL strictement séparées</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-500 dark:text-emerald-400 font-bold">✓</span>
                            <span>Mode support avec audit trail</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-500 dark:text-emerald-400 font-bold">✓</span>
                            <span>Dumps chiffrés et isolés</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-500 dark:text-emerald-400 font-bold">✓</span>
                            <span>Mots de passe hachés bcrypt</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-xl p-5 font-mono text-[11px] space-y-2 text-slate-400 shadow-xl">
                    <div class="text-slate-500">// Commutation de base de données à chaud</div>
                    <div><span class="text-indigo-400">$tenant</span> = <span class="text-amber-400">TenantResolver</span>::<span class="text-emerald-400">resolve</span>($request);</div>
                    <div><span class="text-indigo-400">$database</span> = <span class="text-slate-200">"kamerstock_tenant_"</span> . <span class="text-indigo-400">$tenant</span>->slug;</div>
                    <div class="text-slate-500 pt-2">// Isolation validée sans fuite inter-commerces</div>
                    <div class="text-emerald-400 font-semibold">✓ Connection: [tenant_dedicated] OK</div>
                </div>
            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 8. PLANS & TARIFS (Linear Minimalist Pricing Table) -->
    <!-- ========================================================================= -->
    <section id="pricing" class="py-24 border-t border-slate-200/80 bg-slate-100/40 dark:border-white/[0.06] dark:bg-slate-950/60 relative z-10 transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-14 space-y-3">
                <span class="text-xs font-semibold text-amber-500 dark:text-amber-400 font-mono uppercase tracking-wider">Tarification Transparente</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white font-outfit tracking-tight">
                    Choisissez la formule adaptée à votre échelle.
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 font-light">
                    Facturation en Francs CFA (FCFA). Économisez 2 mois avec l'engagement annuel.
                </p>

                <!-- Billing Toggle -->
                <div class="pt-4 flex items-center justify-center gap-3">
                    <span class="text-xs font-medium" :class="pricingBilling === 'monthly' ? (darkMode ? 'text-white' : 'text-slate-900 font-bold') : 'text-slate-500'">Mensuel</span>
                    <button type="button" 
                            @click="pricingBilling = (pricingBilling === 'monthly' ? 'yearly' : 'monthly')"
                            class="w-11 h-6 rounded-full transition duration-200 relative focus:outline-none"
                            :class="pricingBilling === 'yearly' ? 'bg-amber-500' : (darkMode ? 'bg-slate-800' : 'bg-slate-300')">
                        <div class="w-5 h-5 rounded-full bg-white shadow transition-transform duration-200"
                             :class="pricingBilling === 'yearly' ? 'translate-x-5' : 'translate-x-0.5'"></div>
                    </button>
                    <span class="text-xs font-medium flex items-center gap-1.5" :class="pricingBilling === 'yearly' ? (darkMode ? 'text-white' : 'text-slate-900 font-bold') : 'text-slate-500'">
                        <span>Annuel</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-500 border border-amber-500/20">-17%</span>
                    </span>
                </div>
            </div>

            <!-- Pricing Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Plan 1 : Starter -->
                <div class="linear-card rounded-2xl p-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500 font-outfit">Boutique Solo</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-1 font-outfit">Starter</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-light mt-1 mb-6">Pour une boutique indépendante.</p>

                        <div class="mb-6">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white font-outfit" x-text="pricingBilling === 'monthly' ? '15 000' : '150 000'">15 000</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono"> FCFA / <span x-text="pricingBilling === 'monthly' ? 'mois' : 'an'">mois</span></span>
                        </div>

                        <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 mb-8 font-light">
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span><strong>1</strong> boutique étanche</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Jusqu'à <strong>3</strong> caissiers & vendeurs</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Caisse POS & Reçus thermiques</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Gestion complète des stocks</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="w-full py-2.5 text-center bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] dark:hover:bg-white/[0.08] text-slate-900 dark:text-white text-xs font-semibold rounded-xl border border-slate-200 dark:border-white/[0.08] transition">
                        Démarrer l'essai gratuit
                    </a>
                </div>

                <!-- Plan 2 : Pro (Featured) -->
                <div class="linear-card rounded-2xl p-7 flex flex-col justify-between border-amber-500 shadow-xl shadow-amber-500/10 relative bg-amber-50/30 dark:bg-slate-900/70">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-amber-500 text-slate-950 text-[10px] font-black rounded-full uppercase tracking-wider font-outfit shadow-sm">
                        Le Plus Recommandé
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 font-outfit">Croissance & Équipe</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-1 font-outfit">Pro</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-light mt-1 mb-6">Pour les commerces à fort volume.</p>

                        <div class="mb-6">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white font-outfit" x-text="pricingBilling === 'monthly' ? '30 000' : '300 000'">30 000</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono"> FCFA / <span x-text="pricingBilling === 'monthly' ? 'mois' : 'an'">mois</span></span>
                        </div>

                        <ul class="space-y-2.5 text-xs text-slate-700 dark:text-slate-200 mb-8 font-light">
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500 font-bold">✓</span>
                                <span>Jusqu'à <strong>3</strong> succursales</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500 font-bold">✓</span>
                                <span><strong>10</strong> caissiers & utilisateurs</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500 font-bold">✓</span>
                                <span>Suivi complet des crédits clients</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500 font-bold">✓</span>
                                <span>Portail CRM clients partenaires</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500 font-bold">✓</span>
                                <span>Rapports financiers avancés</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="w-full py-2.5 text-center bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-xl transition shadow-lg shadow-amber-500/20 font-outfit">
                        Essayer Pro pendant 14 jours
                    </a>
                </div>

                <!-- Plan 3 : Business (Réseaux) -->
                <div class="linear-card rounded-2xl p-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500 font-outfit">Réseaux & Franchises</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mt-1 font-outfit">Business</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-light mt-1 mb-6">Pour les groupes multi-succursales.</p>

                        <div class="mb-6">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white font-outfit" x-text="pricingBilling === 'monthly' ? '60 000' : '600 000'">60 000</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-mono"> FCFA / <span x-text="pricingBilling === 'monthly' ? 'mois' : 'an'">mois</span></span>
                        </div>

                        <ul class="space-y-2.5 text-xs text-slate-600 dark:text-slate-300 mb-8 font-light">
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Jusqu'à <strong>10</strong> magasins</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Utilisateurs & vendeurs illimités</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Domaine web personnalisé</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Sauvegardes automatiques quotidiennes</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-amber-500">✓</span>
                                <span>Support prioritaire par WhatsApp & appel</span>
                            </li>
                        </ul>
                    </div>

                    <a href="{{ route('register') }}" class="w-full py-2.5 text-center bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] dark:hover:bg-white/[0.08] text-slate-900 dark:text-white text-xs font-semibold rounded-xl border border-slate-200 dark:border-white/[0.08] transition">
                        Démarrer l'essai Business
                    </a>
                </div>

            </div>

        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 9. FAQ ACCORDION (Linear Minimalist Accordion) -->
    <!-- ========================================================================= -->
    <section id="faq" class="py-24 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <div class="text-center mb-14 space-y-2">
            <span class="text-xs font-semibold text-amber-500 dark:text-amber-400 font-mono uppercase tracking-wider">Réponses Immédiates</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white font-outfit tracking-tight">Questions Fréquentes</h2>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="linear-card rounded-xl p-5 cursor-pointer" @click="activeFaq = (activeFaq === 1 ? null : 1)">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Comment fonctionne l'essai gratuit de 14 jours ?</span>
                    <span class="text-slate-400 dark:text-slate-500 font-mono text-base" x-text="activeFaq === 1 ? '−' : '+'">+</span>
                </div>
                <div x-show="activeFaq === 1" x-collapse x-cloak class="mt-3 text-xs text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                    Dès votre inscription, votre boutique et votre base de données sont provisionnées immédiatement. Vous disposez de l'ensemble des fonctionnalités professionnelles pendant 14 jours. Aucune carte bancaire n'est exigée à la création.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="linear-card rounded-xl p-5 cursor-pointer" @click="activeFaq = (activeFaq === 2 ? null : 2)">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Qu'advient-il de mes données si mon abonnement expire ?</span>
                    <span class="text-slate-400 dark:text-slate-500 font-mono text-base" x-text="activeFaq === 2 ? '−' : '+'">+</span>
                </div>
                <div x-show="activeFaq === 2" x-collapse x-cloak class="mt-3 text-xs text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                    KamerStock bascule votre boutique en <strong>mode lecture seule</strong>. Vous ne perdez aucune donnée : vous pouvez continuer à consulter vos stocks, exporter vos rapports et vérifier vos soldes clients jusqu'au réabonnement.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="linear-card rounded-xl p-5 cursor-pointer" @click="activeFaq = (activeFaq === 3 ? null : 3)">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Pourquoi chaque boutique a-t-elle sa propre base de données ?</span>
                    <span class="text-slate-400 dark:text-slate-500 font-mono text-base" x-text="activeFaq === 3 ? '−' : '+'">+</span>
                </div>
                <div x-show="activeFaq === 3" x-collapse x-cloak class="mt-3 text-xs text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                    C'est la garantie absolue de confidentialité. Vos stocks, vos marges et votre portefeuille de clients ne sont jamais stockés dans la même table qu'un concurrent. C'est l'isolation maximale disponible sur le marché.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="linear-card rounded-xl p-5 cursor-pointer" @click="activeFaq = (activeFaq === 4 ? null : 4)">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">Puis-je gérer plusieurs points de vente ou magasins ?</span>
                    <span class="text-slate-400 dark:text-slate-500 font-mono text-base" x-text="activeFaq === 4 ? '−' : '+'">+</span>
                </div>
                <div x-show="activeFaq === 4" x-collapse x-cloak class="mt-3 text-xs text-slate-600 dark:text-slate-400 font-light leading-relaxed">
                    Oui. Avec les forfaits Pro et Business, vous pouvez créer plusieurs succursales, gérer des transferts d'articles entre magasins et consolider la trésorerie globale de votre entreprise.
                </div>
            </div>
        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- 10. FINAL CALL TO ACTION (Linear Glow Banner) -->
    <!-- ========================================================================= -->
    <section class="py-24 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <div class="linear-card rounded-3xl p-10 sm:p-16 border border-amber-500/40 relative overflow-hidden bg-gradient-to-b from-amber-50/50 to-white dark:from-slate-900/90 dark:to-slate-950 shadow-xl shadow-amber-500/5">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-28 bg-amber-500/20 blur-3xl rounded-full pointer-events-none"></div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 dark:text-white font-outfit tracking-tight">
                Modernisez votre gestion commerciale dès aujourd'hui.
            </h2>
            <p class="mt-4 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-xl mx-auto font-light leading-relaxed">
                Créez votre boutique en moins de 2 minutes. Rejoignez les professionnels qui gagnent en rigueur, en vitesse et en sérénité.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                <a href="{{ route('register') }}" 
                   class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 text-xs font-bold rounded-xl transition shadow-xl shadow-amber-500/25 font-outfit">
                    Créer ma boutique gratuitement
                </a>
                <a href="{{ route('login') }}" 
                   class="w-full sm:w-auto px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-800 dark:bg-white/[0.04] dark:hover:bg-white/[0.08] dark:text-slate-300 text-xs font-semibold rounded-xl border border-slate-200 dark:border-white/[0.08] transition">
                    Connexion gérant
                </a>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 11. FOOTER (Linear Minimalist Multi-Column) -->
    <!-- ========================================================================= -->
    <footer class="border-t border-slate-200 bg-white dark:border-white/[0.06] dark:bg-slate-950 py-12 relative z-10 text-xs transition-colors duration-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-100 dark:border-white/[0.04]">
                <!-- Brand Info -->
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center font-bold text-slate-950 text-sm font-outfit">
                        K
                    </div>
                    <span class="font-bold text-slate-900 dark:text-white font-outfit">KamerStock SaaS</span>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <span class="text-slate-500 dark:text-slate-400">Système de gestion commerciale multi-boutiques</span>
                </div>

                <!-- Fast Links -->
                <div class="flex items-center gap-5 text-slate-600 dark:text-slate-400">
                    <a href="#features" class="hover:text-slate-900 dark:hover:text-white transition">Fonctionnalités</a>
                    <a href="#multitenant" class="hover:text-slate-900 dark:hover:text-white transition">Multi-Boutiques</a>
                    <a href="#pricing" class="hover:text-slate-900 dark:hover:text-white transition">Tarifs</a>
                    <a href="{{ route('landlord.login') }}" class="hover:text-amber-500 dark:hover:text-amber-400 transition">Landlord</a>
                </div>
            </div>

            <!-- Subfooter / Copyright & Lang -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-500 text-[11px]">
                <span>&copy; {{ date('Y') }} KamerStock. Conçu pour la performance, l'isolation et la précision commerciale.</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('lang.switch', 'fr') }}" class="hover:text-slate-900 dark:hover:text-white transition {{ app()->getLocale() === 'fr' ? 'text-amber-500 font-semibold' : '' }}">Français</a>
                    <span>•</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="hover:text-slate-900 dark:hover:text-white transition {{ app()->getLocale() === 'en' ? 'text-amber-500 font-semibold' : '' }}">English</a>
                </div>
            </div>

        </div>
    </footer>

</body>
</html>
