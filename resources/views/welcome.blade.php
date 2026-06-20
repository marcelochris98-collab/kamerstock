<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamerStock | Logiciel Professionnel de Gestion de Stock & CRM pour Quincailleries</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS & JS Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }
        [x-cloak] { display: none !important; }

        /* Subtle Grid Pattern */
        .bg-grid-pattern {
            background-size: 60px 60px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased overflow-x-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Subtle background pattern -->
    <div class="absolute inset-0 bg-grid-pattern pointer-events-none z-0"></div>

    <!-- Header / Navbar -->
    <header class="relative z-50 border-b border-slate-900 bg-slate-950/80 backdrop-blur-md sticky top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 flex items-center justify-between">
            <!-- Logo -->
            <a href="#" class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center font-black text-slate-950 font-outfit text-base tracking-tight shadow-sm shadow-amber-500/20">
                    K
                </div>
                <div>
                    <span class="text-sm font-bold text-white font-outfit tracking-wide block leading-none">KamerStock</span>
                    <span class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase block mt-0.5">Logiciel de gestion</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-400">
                <a href="#fonctionnalites" class="hover:text-white transition">Fonctionnalités</a>
                <a href="#atouts" class="hover:text-white transition">Atouts</a>
                <a href="#faq" class="hover:text-white transition">FAQ</a>
            </nav>

            <!-- CTA Buttons -->
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" 
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-lg transition shadow-lg shadow-amber-500/10">
                        Tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition">
                        Se connecter
                    </a>
                    <a href="{{ route('login') }}" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition border border-slate-800 hover:border-slate-700">
                        Accéder à l'application
                    </a>
                @endauth
            </div>

            <!-- Hamburger Button (Mobile) -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-slate-400 hover:text-white focus:outline-none transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-cloak/>
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-b border-slate-900 bg-slate-950 px-4 pt-2 pb-6 space-y-4"
             x-cloak>
            <nav class="flex flex-col gap-3 text-xs font-semibold text-slate-400">
                <a @click="mobileMenuOpen = false" href="#fonctionnalites" class="hover:text-white py-2 transition border-b border-slate-900/50">Fonctionnalités</a>
                <a @click="mobileMenuOpen = false" href="#atouts" class="hover:text-white py-2 transition border-b border-slate-900/50">Atouts</a>
                <a @click="mobileMenuOpen = false" href="#faq" class="hover:text-white py-2 transition">FAQ</a>
            </nav>
            <div class="pt-4 flex flex-col gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" 
                        class="w-full text-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-lg transition">
                        Tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                        class="w-full text-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition border border-slate-800">
                        Se connecter
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative z-10 pt-16 pb-20 md:pt-24 md:pb-28 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Text -->
            <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 border border-slate-800 rounded-full text-[11px] font-semibold text-slate-300 tracking-wide">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Gestion de Stock & CRM Commercial
                </div>
                
                <h1 class="text-4xl sm:text-5xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-white font-outfit leading-[1.1]">
                    Pilotez votre commerce avec une <span class="text-amber-500">précision rigoureuse</span>.
                </h1>
                
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed font-light">
                    KamerStock est une plateforme professionnelle conçue pour structurer l'activité des quincailleries et commerces. Automatisez vos inventaires, tracez chaque vente, gérez les crédits clients et facilitez les approvisionnements fournisseurs.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-lg transition shadow-lg shadow-amber-500/10 flex items-center justify-center gap-2">
                            Entrer dans l'application
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-lg transition shadow-lg shadow-amber-500/10 flex items-center justify-center gap-2">
                            Accéder à KamerStock
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="#fonctionnalites" 
                           class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-lg transition border border-slate-800 flex items-center justify-center">
                            Découvrir les fonctionnalités
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Hero Graphic (Realistic MacBook & iPhone Mockup) -->
            <div class="lg:col-span-6 flex items-center justify-center">
                <div class="relative w-full max-w-xl">
                    <img src="/devices_mockup.png" alt="KamerStock Dashboard on MacBook Air and iPhone" class="w-full h-auto drop-shadow-2xl rounded-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Key Metrics / Stats Section -->
    <section class="border-y border-slate-900 bg-slate-950/50 py-12 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="space-y-2">
                    <span class="block text-3xl font-extrabold text-white font-outfit">80%</span>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">de gain de temps lors des inventaires physiques</span>
                </div>
                <div class="space-y-2 border-t md:border-t-0 md:border-x border-slate-900 pt-6 md:pt-0">
                    <span class="block text-3xl font-extrabold text-white font-outfit">0%</span>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">d'erreurs de caisse grâce à la traçabilité des ventes</span>
                </div>
                <div class="space-y-2 border-t md:border-t-0 pt-6 md:pt-0">
                    <span class="block text-3xl font-extrabold text-white font-outfit">100%</span>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">de transparence sur les encours et crédits clients</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fonctionnalites" class="relative z-10 py-20 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">Modules Applicatifs</h2>
                <p class="text-3xl font-extrabold text-white font-outfit tracking-tight">
                    Une architecture logicielle complète.
                </p>
                <p class="text-slate-400 text-xs sm:text-sm font-light leading-relaxed">
                    KamerStock regroupe tous les outils professionnels nécessaires pour piloter votre activité commerciale de bout en bout.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Feature 1 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Catalogue & Produits</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Gestion rigoureuse du catalogue avec codes-barres, seuils d'alerte configurables et grilles tarifaires multiples (prix détail, gros et revendeur).
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Flux de Stock</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Enregistrement systématique des entrées, sorties, casses et pertes. Historique complet des mouvements pour éliminer toute disparition de marchandise.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Seuils et Alertes</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Notification immédiate en cas de rupture imminente. Visualisation sur écran et signaux sonores pour anticiper les commandes fournisseurs.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Inventaire Physique</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Module de rapprochement pour saisir les comptages manuels. Le logiciel calcule automatiquement les écarts de stock et valorise les différences.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Approvisionnement</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Création de bons de commande, suivi des réceptions de marchandise, gestion des dettes fournisseurs et historique détaillé des paiements.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Facturation & POS</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Vente rapide au comptant ou à crédit, conversion de devis proforma, génération de reçus PDF et clôtures de caisse par collaborateur.
                    </p>
                </div>

                <!-- Feature 7 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Portail Client (CRM)</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Espace web sécurisé pour vos clients réguliers. Ils y consultent leurs factures d'achats, l'état de leurs crédits et échangent avec vous par messagerie.
                    </p>
                </div>

                <!-- Feature 8 -->
                <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition duration-300">
                    <div class="w-9 h-9 rounded-lg bg-slate-950 border border-slate-800 text-amber-500 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Sécurité & Audit</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Attribution des droits d'accès par rôles (administrateur, caissier, magasinier) et journal d'audit répertoriant chaque action sensible.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Why choose / Atouts Section -->
    <section id="atouts" class="relative z-10 py-20 bg-slate-900/20 border-y border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">Atouts Logicielles</h2>
                <p class="text-3xl font-extrabold text-white font-outfit tracking-tight">Pourquoi adopter KamerStock ?</p>
                <p class="text-slate-400 text-xs sm:text-sm font-light">Découvrez comment notre solution rationalise et sécurise votre gestion quotidienne.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Atout 1 -->
                <div class="flex gap-4 p-6 bg-slate-950 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-800 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Productivité de vos équipes</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            La facturation automatique, les devis proforma instantanés et la consultation en direct des fiches fournisseurs vous permettent d'éviter les ressaisies manuelles chronophages.
                        </p>
                    </div>
                </div>

                <!-- Atout 2 -->
                <div class="flex gap-4 p-6 bg-slate-950 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-800 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Contrôle et Valorisation</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            L'obligation de motiver chaque ajustement (casse, écart de stock) et la journalisation des modifications suppriment les disparitions de produits et les erreurs financières.
                        </p>
                    </div>
                </div>

                <!-- Atout 3 -->
                <div class="flex gap-4 p-6 bg-slate-950 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-800 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Fidélisation et autonomie client</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            Grâce au portail client public, vos clients professionnels consultent en temps réel leurs encours de crédit et historique de facturation, réduisant vos appels de support administratif.
                        </p>
                    </div>
                </div>

                <!-- Atout 4 -->
                <div class="flex gap-4 p-6 bg-slate-950 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-800 text-amber-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Indicateurs d'Aide à la Décision</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            Identifiez immédiatement les produits à forte rotation (meilleures ventes) et les produits dormants pour optimiser la trésorerie et guider vos négociations d'achats.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="relative z-10 py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">Retours d'Expérience</h2>
            <p class="text-3xl font-extrabold text-white font-outfit tracking-tight">Adopté par les professionnels du secteur.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Testimonial 1 -->
            <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-6 relative">
                <span class="text-5xl text-slate-800 font-serif absolute right-6 top-4 select-none">“</span>
                <p class="text-slate-400 text-xs leading-relaxed font-light relative z-10 mb-6">
                    "KamerStock a structuré notre gestion. Auparavant, les inventaires prenaient plusieurs jours par mois avec des écarts financiers fréquents. Désormais, le stock est suivi en temps réel et les alertes nous évitent des ruptures de stock critiques."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-950 border border-slate-800 text-amber-500 rounded-lg flex items-center justify-center font-bold text-sm">
                        FK
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-white leading-none">Fouda Kamga</span>
                        <span class="block text-[9px] text-slate-500 mt-1">Directeur, Quincaillerie Générale Kamga</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-6 relative">
                <span class="text-5xl text-slate-800 font-serif absolute right-6 top-4 select-none">“</span>
                <p class="text-slate-400 text-xs leading-relaxed font-light relative z-10 mb-6">
                    "La gestion des crédits et encours clients était source d'erreurs et de tensions. KamerStock bloque automatiquement les ventes si les limites de crédit sont atteintes. Le portail client permet à nos partenaires de suivre leurs échéances."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-950 border border-slate-800 text-amber-500 rounded-lg flex items-center justify-center font-bold text-sm">
                        MN
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-white leading-none">Marie Ngo</span>
                        <span class="block text-[9px] text-slate-500 mt-1">Gérante, Ets Bâtiment Service</span>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-6 relative">
                <span class="text-5xl text-slate-800 font-serif absolute right-6 top-4 select-none">“</span>
                <p class="text-slate-400 text-xs leading-relaxed font-light relative z-10 mb-6">
                    "L'application est très simple d'utilisation pour notre personnel en caisse. La distinction entre les tarifs de gros et de détail lors de la facturation fonctionne en un clic. C'est un outil indispensable pour les commerces de quincaillerie."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-950 border border-slate-800 text-amber-500 rounded-lg flex items-center justify-center font-bold text-sm">
                        OM
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-white leading-none">Ousman Mey</span>
                        <span class="block text-[9px] text-slate-500 mt-1">Propriétaire, Quincaillerie du Centre</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="relative z-10 py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeFaq: null }">
        <div class="text-center space-y-4 mb-16">
            <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">FAQ</h2>
            <p class="text-3xl font-extrabold text-white font-outfit tracking-tight">Questions Fréquentes.</p>
        </div>

        <div class="space-y-4 divide-y divide-slate-900">
            <!-- FAQ 1 -->
            <div class="pt-4 first:pt-0">
                <button @click="activeFaq = (activeFaq === 1 ? null : 1)" 
                    class="w-full flex items-center justify-between text-left py-3 focus:outline-none transition group">
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition">Comment fonctionne le portail client ?</span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="activeFaq === 1 ? 'rotate-180 text-amber-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeFaq === 1" x-transition x-cloak class="pb-4 text-xs text-slate-400 font-light leading-relaxed">
                    Le portail client permet à vos clients de se connecter de manière autonome pour consulter leur historique d'achats, l'état précis de leurs crédits (somme due, échéancier) et échanger par messages avec votre service d'administration.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="pt-4">
                <button @click="activeFaq = (activeFaq === 2 ? null : 2)" 
                    class="w-full flex items-center justify-between text-left py-3 focus:outline-none transition group">
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition">Les alertes de stock sont-elles réelles ?</span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="activeFaq === 2 ? 'rotate-180 text-amber-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeFaq === 2" x-transition x-cloak class="pb-4 text-xs text-slate-400 font-light leading-relaxed">
                    Oui. Dès que le stock d'un produit passe en dessous du seuil d'alerte configuré, une notification visuelle s'affiche immédiatement sur le tableau de bord des collaborateurs et un signal sonore peut retentir pour avertir l'équipe.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="pt-4">
                <button @click="activeFaq = (activeFaq === 3 ? null : 3)" 
                    class="w-full flex items-center justify-between text-left py-3 focus:outline-none transition group">
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition">Le logiciel gère-t-il plusieurs tarifs ?</span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="activeFaq === 3 ? 'rotate-180 text-amber-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeFaq === 3" x-transition x-cloak class="pb-4 text-xs text-slate-400 font-light leading-relaxed">
                    Oui, KamerStock supporte jusqu'à 4 tarifs différents par article : prix de détail, prix de gros, prix spécial revendeur et prix entreprise, applicables instantanément lors de la vente.
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="relative z-10 border-t border-slate-900 bg-slate-950 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Logo + Copyright -->
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-slate-900 border border-slate-800 text-amber-500 rounded flex items-center justify-center font-bold text-xs">
                    K
                </div>
                <span class="text-xs text-slate-500">© {{ date('Y') }} KamerStock. Tous droits réservés.</span>
            </div>

            <!-- Login shortcut -->
            <div>
                <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-450 hover:text-white transition">
                    Accéder à l'espace de connexion →
                </a>
            </div>
        </div>
    </footer>

</body>
</html>
