<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamerStock | Solution Professionnelle de Gestion de Stock & CRM</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;850&display=swap" rel="stylesheet">
    
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
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased overflow-x-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Glowing Background Effect -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[600px] pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-[300px] left-[10%] w-[500px] h-[500px] rounded-full bg-amber-500/10 blur-[120px]"></div>
        <div class="absolute -top-[250px] right-[10%] w-[600px] h-[600px] rounded-full bg-blue-600/10 blur-[130px]"></div>
    </div>

    <!-- Header / Navbar -->
    <header class="relative z-50 border-b border-slate-900 bg-slate-950/70 backdrop-blur-md sticky top-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Logo -->
            <a href="#" class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center font-black text-slate-950 font-outfit text-base tracking-tight shadow-md shadow-amber-500/20">
                    K
                </div>
                <div>
                    <span class="text-sm font-bold text-white font-outfit tracking-wide block leading-none">KamerStock</span>
                    <span class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase block mt-0.5">Logiciel de gestion</span>
                </div>
            </a>

            <!-- Navigation Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-slate-400">
                <a href="#fonctionnalites" class="hover:text-white transition">Fonctionnalités</a>
                <a href="#atouts" class="hover:text-white transition">Atouts logicielles</a>
                <a href="#faq" class="hover:text-white transition">FAQ</a>
            </nav>

            <!-- CTA Buttons (Desktop) -->
            <div class="hidden md:flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" 
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-955 text-xs font-bold rounded-lg transition shadow-lg shadow-amber-500/10">
                        Tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition">
                        Se connecter
                    </a>
                    <a href="{{ route('login') }}" 
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition border border-slate-700 hover:border-slate-600">
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
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
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
                        class="w-full text-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-955 text-xs font-bold rounded-lg transition">
                        Tableau de bord
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                        class="w-full text-center px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition border border-slate-700">
                        Se connecter
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative z-10 pt-16 pb-20 md:pt-28 md:pb-32 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Text -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-500/10 border border-amber-500/20 rounded-full text-[11px] font-semibold text-amber-400 tracking-wide">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Gestion de stock & CRM intelligente
                </div>
                
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight text-white font-outfit leading-[1.1]">
                    Le moteur de performance <br class="hidden sm:inline">
                    de votre <span class="bg-gradient-to-r from-amber-400 via-orange-500 to-amber-500 bg-clip-text text-transparent">commerce</span>.
                </h1>
                
                <p class="text-slate-400 text-sm sm:text-base max-w-2xl mx-auto lg:mx-0 leading-relaxed font-light">
                    KamerStock est une plateforme tout-en-un conçue pour piloter les stocks, analyser les ventes, gérer les approvisionnements et fidéliser vos clients en temps réel dans votre quincaillerie . Simple, rapide et extrêmement précis.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                    @auth
                        <a href="{{ route('dashboard') }}" 
                           class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-955 text-xs font-bold rounded-lg transition shadow-lg shadow-amber-500/10 flex items-center justify-center gap-2">
                            Entrer dans l'application
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-slate-955 text-xs font-bold rounded-lg transition shadow-lg shadow-amber-500/10 flex items-center justify-center gap-2">
                            Commencer l'expérience
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7M5 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="#fonctionnalites" 
                           class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-lg transition border border-slate-880 hover:border-slate-700 flex items-center justify-center">
                            Découvrir les fonctionnalités
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Hero Graphic (Animated Stock Icon / Crate / Gears) -->
            <div class="lg:col-span-5 flex items-center justify-center relative min-h-[350px]">
                <!-- Outer orbital ring -->
                <div class="absolute w-[300px] h-[300px] border border-dashed border-slate-800 rounded-full animate-[spin_40s_linear_infinite]"></div>
                <!-- Inner orbital ring -->
                <div class="absolute w-[220px] h-[220px] border border-slate-800/60 rounded-full animate-[spin_20s_linear_infinite_reverse]"></div>
                
                <!-- Glowing effect background -->
                <div class="absolute w-[200px] h-[200px] bg-amber-500/10 rounded-full blur-[80px] z-0"></div>

                <!-- Main dynamic SVG container -->
                <svg class="w-64 h-64 z-10 drop-shadow-2xl animate-[float_6s_ease-in-out_infinite]" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Definitions for gradients and clipping -->
                    <defs>
                        <linearGradient id="cube-top-grad" x1="100" y1="45" x2="100" y2="95" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#fbbf24"/> <!-- amber-400 -->
                            <stop offset="100%" stop-color="#d97706"/> <!-- amber-600 -->
                        </linearGradient>
                        <linearGradient id="cube-left-grad" x1="50" y1="95" x2="100" y2="155" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#1e293b"/> <!-- slate-800 -->
                            <stop offset="100%" stop-color="#0f172a"/> <!-- slate-900 -->
                        </linearGradient>
                        <linearGradient id="cube-right-grad" x1="150" y1="95" x2="100" y2="155" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#334155"/> <!-- slate-700 -->
                            <stop offset="100%" stop-color="#1e293b"/> <!-- slate-800 -->
                        </linearGradient>
                        
                        <!-- Floating particles animations -->
                        <style>
                            @keyframes float {
                                0%, 100% { transform: translateY(0px) rotate(0deg); }
                                50% { transform: translateY(-15px) rotate(2deg); }
                            }
                            @keyframes rotate-gear {
                                0% { transform: rotate(0deg); }
                                100% { transform: rotate(360deg); }
                            }
                            @keyframes rotate-reverse {
                                0% { transform: rotate(0deg); }
                                100% { transform: rotate(-360deg); }
                            }
                            @keyframes pulse-light {
                                0%, 100% { opacity: 0.3; }
                                50% { opacity: 0.8; }
                            }
                            .animated-gear {
                                transform-origin: 100px 100px;
                                animation: rotate-gear 25s linear infinite;
                            }
                            .animated-gear-small {
                                transform-origin: 55px 65px;
                                animation: rotate-reverse 15s linear infinite;
                            }
                            .orbital-dot {
                                animation: pulse-light 3s ease-in-out infinite;
                            }
                        </style>
                    </defs>

                    <!-- Background Logistics Gear (Rotating) -->
                    <g class="animated-gear opacity-20">
                        <circle cx="100" cy="100" r="70" stroke="#475569" stroke-width="2" stroke-dasharray="10 5"/>
                        <circle cx="100" cy="100" r="50" stroke="#475569" stroke-width="1.5"/>
                        <!-- Gear teeth rotated around 100 100 -->
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(0 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(30 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(60 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(90 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(120 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(150 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(180 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(210 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(240 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(270 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(300 100 100)"/>
                        <rect x="95" y="24" width="10" height="12" rx="2" fill="#475569" transform="rotate(330 100 100)"/>
                    </g>

                    <!-- Small Secondary Gear (Rotating reverse) -->
                    <g class="animated-gear-small opacity-15">
                        <circle cx="55" cy="65" r="25" stroke="#475569" stroke-width="1.5" stroke-dasharray="6 3"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(0 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(45 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(90 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(135 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(180 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(225 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(270 55 65)"/>
                        <rect x="52" y="37" width="6" height="8" rx="1" fill="#475569" transform="rotate(315 55 65)"/>
                    </g>

                    <!-- 3D Crate/Box representing Inventory/Quincaillerie Stock -->
                    <g class="drop-shadow-xl">
                        <!-- Top Face (Amber) -->
                        <polygon points="100,45 150,70 100,95 50,70" fill="url(#cube-top-grad)" />
                        <!-- Left Face (Slate Dark) -->
                        <polygon points="50,70 100,95 100,155 50,130" fill="url(#cube-left-grad)" />
                        <!-- Right Face (Slate Medium) -->
                        <polygon points="100,95 150,70 150,130 100,155" fill="url(#cube-right-grad)" />

                        <!-- Wood/Metal crate grid markings (crate/package look) -->
                        <!-- Top face lines -->
                        <line x1="100" y1="45" x2="100" y2="95" stroke="#fbbf24" stroke-width="1" opacity="0.3"/>
                        <line x1="50" y1="70" x2="150" y2="70" stroke="#fbbf24" stroke-width="1" opacity="0.3"/>
                        <!-- Left face lines -->
                        <line x1="50" y1="70" x2="100" y2="155" stroke="#475569" stroke-width="1.5" opacity="0.3"/>
                        <line x1="50" y1="130" x2="100" y2="95" stroke="#475569" stroke-width="1.5" opacity="0.3"/>
                        <!-- Right face lines -->
                        <line x1="100" y1="155" x2="150" y2="70" stroke="#64748b" stroke-width="1.5" opacity="0.3"/>
                        <line x1="100" y1="95" x2="150" y2="130" stroke="#64748b" stroke-width="1.5" opacity="0.3"/>

                        <!-- Highlights -->
                        <polygon points="100,45 150,70 100,95 50,70" fill="#ffffff" opacity="0.1" />
                        <!-- Crate borders -->
                        <polygon points="100,45 150,70 100,95 50,70" stroke="#fbbf24" stroke-width="1.5" fill="none" opacity="0.6"/>
                        <polygon points="50,70 100,95 100,155 50,130" stroke="#334155" stroke-width="1.5" fill="none" opacity="0.4"/>
                        <polygon points="100,95 150,70 150,130 100,155" stroke="#475569" stroke-width="1.5" fill="none" opacity="0.4"/>
                    </g>

                    <!-- Orbiting dots representing items tracking -->
                    <circle cx="100" cy="25" r="4" fill="#fbbf24" class="orbital-dot" style="animation-delay: 0.5s;"/>
                    <circle cx="40" cy="110" r="3" fill="#3b82f6" class="orbital-dot" style="animation-delay: 1.2s;"/>
                    <circle cx="160" cy="120" r="5" fill="#10b981" class="orbital-dot" style="animation-delay: 0s;"/>
                    <circle cx="145" cy="55" r="3" fill="#ec4899" class="orbital-dot" style="animation-delay: 2s;"/>

                    <!-- Curving Arrow orbit indicator -->
                    <path d="M 30,135 A 85,85 0 0,1 170,65" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-dasharray="8 6" opacity="0.5"/>
                    <path d="M 170,135 A 85,85 0 0,1 30,65" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-dasharray="8 6" opacity="0.4"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fonctionnalites" class="relative z-10 py-20 bg-slate-900/40 border-y border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">Fonctionnalités avancées</h2>
                <p class="text-3xl sm:text-4xl font-extrabold text-white font-outfit tracking-tight">
                    Une gestion globale conçue pour exceller.
                </p>
                <p class="text-slate-400 text-xs sm:text-sm leading-relaxed font-light">
                    KamerStock intègre tous les modules requis pour automatiser vos opérations de stock et optimiser votre relation client.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Feature 1 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4 group-hover:bg-amber-500 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Catalogue & Produits</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Organisation complète avec codes à barres, prix multiples (gros, revendeur, détail), taux de taxe et seuils d'alerte configurables.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4 group-hover:bg-blue-500 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Mouvements de Stock</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Enregistrement ultra-rapide des entrées, sorties, pertes et casses. Historique complet avec filtres multicritères instantanés.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center mb-4 group-hover:bg-rose-50 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Alertes de seuil critique</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Notifications intelligentes intégrées. Alertes automatiques en cas de rupture ou de stock critique avec signaux audio personnalisables.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4 group-hover:bg-emerald-50 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Inventaire Physique</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Rapprochement de stock périodique. Saisissez votre comptage manuel, KamerStock calcule les écarts et effectue les ajustements de stock.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center mb-4 group-hover:bg-teal-50 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Approvisionnement & Dettes</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Bons de commande fournisseurs, suivi des réceptions d'achats, fiches de dettes et historique détaillé des paiements effectués.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center mb-4 group-hover:bg-purple-50 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Ventes & Facturation</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Devis convertibles en ventes, facturation automatique, reçus PDF imprimables et journal de ventes précis par caissier.
                    </p>
                </div>

                <!-- Feature 7 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4 group-hover:bg-indigo-50 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">CRM & Portail Client</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Espace client sécurisé en ligne. Vos clients peuvent suivre leurs factures, l'état de leurs crédits et communiquer avec vous.
                    </p>
                </div>

                <!-- Feature 8 -->
                <div class="bg-slate-950 border border-slate-900 rounded-xl p-5 hover:border-slate-800 transition group hover:-translate-y-0.5 duration-300">
                    <div class="w-9 h-9 rounded-lg bg-amber-600/10 text-amber-500 flex items-center justify-center mb-4 group-hover:bg-amber-550 group-hover:text-slate-955 transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-white mb-2 font-outfit">Sécurité & Audit</h3>
                    <p class="text-slate-400 text-xs leading-relaxed font-light">
                        Contrôle strict des accès par rôles (permissions) et journal d'audit enregistrant chaque action effectuée par vos collaborateurs.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- Atouts Section (Replaced Technology Section) -->
    <section id="atouts" class="relative z-10 py-20 bg-slate-950 border-b border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">Pourquoi choisir KamerStock ?</h2>
                <p class="text-3xl font-extrabold text-white font-outfit tracking-tight">Les atouts majeurs du logiciel.</p>
                <p class="text-slate-400 text-xs sm:text-sm font-light">Découvrez comment notre application transforme la gestion quotidienne de votre entreprise.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Atout 1 -->
                <div class="flex gap-4 p-6 bg-slate-900/40 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Gain de Temps Phénoménal</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            Grâce à l'automatisation des factures, la génération rapide de devis proforma et la consultation instantanée des fiches fournisseurs ou clients, vous et vos équipes libérez des heures de travail manuel chaque jour.
                        </p>
                    </div>
                </div>

                <!-- Atout 2 -->
                <div class="flex gap-4 p-6 bg-slate-900/40 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Précision et Élimination des Pertes</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            Chaque ajustement de stock ou casse constatée est justifié et journalisé. Le calcul en temps réel de la valorisation de votre stock empêche les erreurs de caisse ou les disparitions inexpliquées de produits.
                        </p>
                    </div>
                </div>

                <!-- Atout 3 -->
                <div class="flex gap-4 p-6 bg-slate-900/40 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Fidélisation Clientèle Accrue</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            Le portail client offre une transparence totale. Vos clients réguliers peuvent vérifier l'état de leur compte, consulter leurs factures et suivre leurs encours de crédits sans avoir besoin de vous appeler.
                        </p>
                    </div>
                </div>

                <!-- Atout 4 -->
                <div class="flex gap-4 p-6 bg-slate-900/40 border border-slate-900 rounded-xl hover:border-slate-800 transition duration-300">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white mb-1.5 font-outfit">Indicateurs de Décision Clairs</h3>
                        <p class="text-slate-400 text-xs leading-relaxed font-light">
                            Grâce aux rapports financiers, aux listes de produits à forte rotation (meilleures ventes) et aux produits dormants (capital immobilisé), vous savez exactement quoi acheter et quand négocier avec vos fournisseurs.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="relative z-10 py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeFaq: null }">
        <div class="text-center space-y-4 mb-16">
            <h2 class="text-xs font-bold text-amber-500 tracking-widest uppercase font-outfit">FAQ</h2>
            <p class="text-3xl font-extrabold text-white font-outfit tracking-tight">Des réponses à vos questions.</p>
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
                    Le portail client permet à vos clients de se connecter avec leurs accès dédiés. Ils peuvent consulter leur historique d'achats, l'état de leurs crédits (montant dû, échéance) et communiquer directement avec le service d'administration via la messagerie en temps réel.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="pt-4">
                <button @click="activeFaq = (activeFaq === 2 ? null : 2)" 
                    class="w-full flex items-center justify-between text-left py-3 focus:outline-none transition group">
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition">Les alertes de stock sont-elles instantanées ?</span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="activeFaq === 2 ? 'rotate-180 text-amber-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeFaq === 2" x-transition x-cloak class="pb-4 text-xs text-slate-400 font-light leading-relaxed">
                    Oui, dès qu'une vente ou une sortie manuelle fait descendre la quantité d'un produit en dessous de son seuil critique d'alerte, une alerte est enregistrée. Les gestionnaires connectés voient l'alerte sur leur écran et un signal sonore retentit pour attirer leur attention.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="pt-4">
                <button @click="activeFaq = (activeFaq === 3 ? null : 3)" 
                    class="w-full flex items-center justify-between text-left py-3 focus:outline-none transition group">
                    <span class="text-sm font-semibold text-slate-200 group-hover:text-white transition">Le système prend-il en compte plusieurs tarifs de vente ?</span>
                    <svg class="w-4 h-4 text-slate-500 transition-transform duration-300" :class="activeFaq === 3 ? 'rotate-180 text-amber-500' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="activeFaq === 3" x-transition x-cloak class="pb-4 text-xs text-slate-400 font-light leading-relaxed">
                    Tout à fait. KamerStock gère jusqu'à 4 grilles tarifaires différentes par produit : prix de détail, prix de gros, prix spécial revendeur et prix entreprise. Lors d'une vente, le vendeur peut appliquer le prix adapté en fonction du profil du client en un seul clic.
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
