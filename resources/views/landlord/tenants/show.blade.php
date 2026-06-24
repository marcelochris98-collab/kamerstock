@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-xs text-slate-400 font-medium">Boutiques / Détails</p>
            <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $tenant->name }}</h1>
        </div>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <a href="{{ route('landlord.tenants.edit', $tenant) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 rounded-xl transition select-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Modifier
            </a>
            
            @if($tenant->status !== 'suspended')
                @if($tenant->status !== 'read_only')
                    <form action="{{ route('landlord.tenants.read_only', $tenant) }}" method="POST" class="inline-block" onsubmit="return confirm('Passer cette boutique en lecture seule ?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-xs font-bold text-white rounded-xl shadow-md shadow-amber-550/10 transition">
                            Lecture Seule
                        </button>
                    </form>
                @endif
                
                <form action="{{ route('landlord.tenants.suspend', $tenant) }}" method="POST" class="inline-block" onsubmit="return confirm('Confirmer la suspension de cette boutique ?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-650 hover:bg-red-700 text-xs font-bold text-white rounded-xl shadow-md shadow-red-655/10 transition">
                        Suspendre
                    </button>
                </form>
            @else
                <form action="{{ route('landlord.tenants.activate', $tenant) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white rounded-xl shadow-md shadow-emerald-650/10 transition">
                        Activer la Boutique
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Credentials success banner --}}
    @if(session('show_credentials') || request()->has('show_credentials'))
        <div class="mb-6 p-5 bg-indigo-950/40 border border-indigo-700/50 text-indigo-200 rounded-2xl shadow-lg flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <h4 class="text-sm font-bold text-white flex items-center gap-1.5">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    Boutique créée et prête à être partagée !
                </h4>
                <p class="text-xs text-indigo-300">Les accès d'administration ont été configurés en mode préparé. Copiez le message ci-dessous pour l'envoyer au propriétaire.</p>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <button type="button" onclick="copyAccessMessage()" class="px-4 py-2 bg-indigo-650 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition select-none flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                    Copier les accès
                </button>
            </div>
        </div>
    @endif

    {{-- Confidentiality Disclaimer --}}
    <div class="mb-6 p-4 bg-slate-900 border border-slate-800 text-slate-400 rounded-xl text-xs flex items-center justify-between shadow-sm select-none">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span><strong>Confidentialité des données</strong> : Les données métier de la boutique (ventes, produits, clients) ne sont pas accessibles depuis cet espace.</span>
        </div>
        <span class="text-[10px] text-slate-550 uppercase tracking-widest font-bold font-mono">KamerStock Platform</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Metadata Card --}}
        <div class="space-y-6">
            
            {{-- General Info Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Détails Système</h3>

                <ul class="space-y-3.5 text-xs">
                    <li class="flex justify-between items-start gap-4">
                        <span class="text-slate-400">UUID</span>
                        <span class="font-mono text-slate-700 text-right select-all">{{ $tenant->uuid }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Slug</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->slug }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Secteur</span>
                        <span class="font-semibold text-slate-800 capitalize">
                            @if($tenant->business_type === 'autre')
                                {{ $tenant->business_type_custom }}
                            @else
                                {{ str_replace('_', ' ', $tenant->business_type ?? 'N/A') }}
                            @endif
                        </span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Statut</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                            @if($tenant->status === 'active') bg-emerald-50 text-emerald-700
                            @elseif($tenant->status === 'trial') bg-indigo-50 text-indigo-750
                            @elseif($tenant->status === 'suspended') bg-red-50 text-red-700
                            @elseif($tenant->status === 'read_only') bg-amber-50 text-amber-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ $tenant->status }}
                        </span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Devise / Timezone</span>
                        <span class="font-semibold text-slate-850">{{ $tenant->currency }} / {{ $tenant->timezone }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Créée le</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Dernière connexion</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->last_login_at ? $tenant->last_login_at->format('d/m/Y H:i') : 'Jamais' }}</span>
                    </li>
                </ul>
            </div>

            {{-- Contact Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Propriétaire de l'instance</h3>

                <ul class="space-y-3.5 text-xs">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Nom complet</span>
                        <span class="font-semibold text-slate-800">{{ $tenant->owner_name ?? 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Adresse e-mail</span>
                        <span class="font-semibold text-slate-800 select-all">{{ $tenant->owner_email ?? 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Téléphone</span>
                        <span class="font-semibold text-slate-800 select-all">{{ $tenant->owner_phone ?? 'N/A' }}</span>
                    </li>
                </ul>
            </div>

            {{-- Provisioning Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Provisionnement</h3>

                <ul class="space-y-3.5 text-xs">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Mode de déploiement</span>
                        <span class="font-semibold text-slate-850">
                            @if(config('platform.database_provisioning.enabled'))
                                <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 font-bold">Réel</span>
                            @else
                                <span class="text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100 font-bold">Préparation seule</span>
                            @endif
                        </span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Nom de la base</span>
                        <span class="font-mono text-slate-700 select-all">{{ $tenant->database_name ?? 'Non définie' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Statut technique</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                            @if($tenant->provisioning_status === 'migrated') bg-emerald-50 text-emerald-700
                            @elseif($tenant->provisioning_status === 'database_created') bg-blue-50 text-blue-750
                            @elseif($tenant->provisioning_status === 'legacy_current_db') bg-indigo-50 text-indigo-750 border border-indigo-150
                            @elseif($tenant->provisioning_status === 'failed') bg-rose-50 text-rose-700 border border-rose-100
                            @else bg-slate-150 text-slate-600 @endif">
                            {{ config("platform.provisioning_modes.{$tenant->provisioning_status}", $tenant->provisioning_status) }}
                        </span>
                    </li>
                    @if($tenant->provisioning_error)
                        <li class="flex flex-col gap-1.5 mt-2">
                            <span class="text-slate-400">Erreur de provisionnement</span>
                            <div class="bg-rose-50/50 border border-rose-100 rounded-xl p-3 font-mono text-[10px] text-rose-700 break-words">
                                {{ $tenant->provisioning_error }}
                            </div>
                        </li>
                    @endif
                </ul>

                <div class="mt-4">
                    @if(in_array($tenant->provisioning_status, ['prepared','failed']) && !empty($tenant->database_name) && $tenant->provisioning_status !== 'legacy_current_db')
                        @if(!config('platform.database_provisioning.enabled'))
                            <div class="mb-3 p-3 bg-amber-50 border border-amber-100 rounded-xl text-amber-800 text-xs">
                                Le provisionnement réel est désactivé. Activez PLATFORM_ENABLE_DB_PROVISIONING=true pour créer réellement la base.
                            </div>
                        @else
                            <div class="mb-3 p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                Cette action va créer la base de données réelle de la boutique si elle n’existe pas.
                            </div>
                        @endif

                        <form action="{{ route('landlord.tenants.provision_database', $tenant) }}" method="POST" onsubmit="return confirm('Créer la base de données réelle pour cette boutique ?')">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-indigo-650 hover:bg-indigo-750 text-white font-bold rounded-xl transition text-[11px]">
                                Créer la base boutique
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Routage / Connexion Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Routage / Connexion</h3>

                <ul class="space-y-3.5 text-xs">
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Slug</span>
                        <span class="font-mono text-slate-700 font-semibold select-all">{{ $tenant->slug }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Domaine</span>
                        <span class="font-semibold text-slate-850 select-all">{{ $tenant->domain ?? 'Aucun' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Sous-domaine</span>
                        <span class="font-semibold text-slate-850 select-all">{{ $tenant->subdomain ?? 'Aucun' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Base de données</span>
                        <span class="font-mono text-slate-700 select-all">{{ $tenant->database_name ?? 'N/A' }}</span>
                    </li>
                    <li class="flex justify-between items-center">
                        <span class="text-slate-400">Mode</span>
                        <span class="font-semibold text-slate-850">
                            @if($tenant->provisioning_status === 'legacy_current_db')
                                Boutique actuelle legacy
                            @elseif($tenant->provisioning_status === 'prepared')
                                Préparation uniquement
                            @elseif($tenant->provisioning_status === 'database_created')
                                Base créée
                            @elseif($tenant->provisioning_status === 'migrated')
                                Migrée
                            @else
                                {{ ucfirst($tenant->provisioning_status) }}
                            @endif
                        </span>
                    </li>
                </ul>

                <div class="mt-4 pt-4 border-t border-slate-150 space-y-2.5">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">URLs de test local</span>
                    <div class="flex flex-col gap-1.5 font-mono text-[10px]">
                        <a href="http://127.0.0.1:8000/tenant-debug?tenant={{ $tenant->slug }}" target="_blank" class="text-indigo-650 hover:text-indigo-850 underline break-all">
                            Debug: /tenant-debug?tenant={{ $tenant->slug }}
                        </a>
                        <a href="http://127.0.0.1:8000/dashboard?tenant={{ $tenant->slug }}" target="_blank" class="text-indigo-650 hover:text-indigo-850 underline break-all">
                            Dashboard: /dashboard?tenant={{ $tenant->slug }}
                        </a>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-150 text-[10px] text-slate-400 leading-relaxed">
                    <svg class="w-3.5 h-3.5 text-indigo-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Le routage par sous-domaine sera activé dans une étape suivante.
                </div>
            </div>

            {{-- Owner Access Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Accès Propriétaire</h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <span class="block text-slate-400 mb-1">E-mail de connexion</span>
                        <span class="font-semibold text-slate-800 font-mono select-all">{{ $tenant->owner_login_email ?? 'N/A' }}</span>
                    </div>

                    @if($tenant->owner_password_plain)
                        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3">
                            <span class="block text-[10px] font-bold text-indigo-750 uppercase tracking-wider mb-1">Accès Propriétaire</span>
                            <div class="flex items-center justify-between bg-white border border-indigo-150 rounded-lg px-2.5 py-1.5 font-mono text-slate-900 text-xs">
                                <span class="select-all">{{ $tenant->owner_password_plain }}</span>
                                <span class="text-[9px] text-slate-400 font-semibold uppercase">Temporaire</span>
                            </div>
                            <p class="text-[10px] text-indigo-650 mt-1.5 leading-tight font-medium">
                                <svg class="w-3.5 h-3.5 text-indigo-600 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                À transmettre une seule fois au propriétaire.
                            </p>
                        </div>
                    @endif

                    <div class="pt-2">
                        <form action="{{ route('landlord.tenants.regenerate_owner_password', $tenant) }}" method="POST" onsubmit="return confirm('Confirmer la régénération du mot de passe temporaire ?')">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition text-[11px] border border-indigo-200">
                                Régénérer le mot de passe
                            </button>
                        </form>
                    </div>

                    @php
                        $future_url = $tenant->subdomain 
                            ? "https://{$tenant->subdomain}.kamerstock.cm" 
                            : "https://kamerstock.cm/{$tenant->slug}";
                        
                        $messageText = "Bonjour,\n\nVotre espace KamerStock est en préparation.\n\nBoutique : {$tenant->name}\nLien : {$future_url}\nEmail : {$tenant->owner_login_email}\nMot de passe temporaire : " . ($tenant->owner_password_plain ?? 'N/A') . "\n\nVous devrez changer votre mot de passe à la première connexion.\n\nCordialement,\nKamerStock";
                    @endphp

                    <div class="pt-4 border-t border-slate-100">
                        <span class="block text-slate-400 mb-2">Message d'accès copiable</span>
                        <textarea id="accessMessageText" readonly rows="7" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-mono text-[10px] text-slate-600 focus:outline-none resize-none">{{ $messageText }}</textarea>
                        
                        <button type="button" onclick="copyAccessMessage()" 
                            class="w-full mt-2 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition text-[11px] flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            Copier le message
                        </button>
                    </div>
                </div>
            </div>

            {{-- Support Access Card --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Accès Support Sécurisé</h3>

                @php
                    $supportService = app(\App\Services\Platform\SupportAccessService::class);
                    $activeAccess = $supportService->activeAccessForTenant($tenant);
                @endphp

                @if($activeAccess)
                    <div class="space-y-4">
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-emerald-850">Accès Support Actif</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold uppercase text-[9px]">Actif</span>
                            </div>
                            <p class="text-slate-650 mb-1"><strong>Raison :</strong> {{ $activeAccess->reason }}</p>
                            <p class="text-slate-650 mb-1"><strong>Début :</strong> {{ $activeAccess->starts_at->format('d/m/Y H:i') }}</p>
                            <p class="text-slate-650"><strong>Fin :</strong> {{ $activeAccess->ends_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="flex flex-col gap-2">
                            <a href="{{ route('landlord.support.enter', $activeAccess) }}" class="w-full py-2 bg-indigo-650 hover:bg-indigo-700 text-white font-bold rounded-xl text-center transition text-xs shadow-md shadow-indigo-650/10 select-none">
                                Entrer en support
                            </a>
                            <form action="{{ route('landlord.support.revoke', $activeAccess) }}" method="POST" onsubmit="return confirm('Confirmer la révocation immédiate de cet accès support ?')">
                                @csrf
                                <button type="submit" class="w-full py-2 bg-red-50 hover:bg-red-100 text-red-650 font-bold rounded-xl transition text-xs border border-red-200 select-none">
                                    Révoquer l'accès
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form action="{{ route('landlord.tenants.support.store', $tenant) }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label for="reason" class="block text-slate-400 mb-1">Motif de l'intervention</label>
                            <input type="text" id="reason" name="reason" required placeholder="Ex: Résolution bug affichage dashboard" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="duration" class="block text-slate-400 mb-1">Durée de l'accès</label>
                            <select id="duration" name="duration" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-850 focus:outline-none focus:border-indigo-500">
                                <option value="30_minutes">30 minutes</option>
                                <option value="1_hour">1 heure</option>
                                <option value="24_hours">24 heures</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-850 text-white font-bold rounded-xl transition select-none">
                            Créer et Activer l'accès
                        </button>
                    </form>
                @endif

                <div class="mt-4 pt-4 border-t border-slate-100 text-[10px] text-slate-450 leading-relaxed">
                    <svg class="w-3.5 h-3.5 text-slate-400 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Cet accès est temporaire et journalisé. Il ne donne pas un droit permanent sur les données de la boutique.
                </div>
            </div>

        </div>

        {{-- Right: Subscriptions, Payments, Backups & Audit Logs --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Subscriptions --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Historique d'abonnements</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Plan</th>
                                <th class="py-2">Période</th>
                                <th class="py-2">Montant</th>
                                <th class="py-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->subscriptions as $sub)
                                <tr>
                                    <td class="py-3 font-semibold text-slate-850">{{ $sub->plan?->name ?? 'N/A' }}</td>
                                    <td class="py-3">
                                        Du {{ $sub->starts_at?->format('d/m/Y') }} au {{ $sub->ends_at?->format('d/m/Y') }}
                                        @if($sub->trial_ends_at)
                                            <span class="block text-[10px] text-indigo-650">Essai gratuit jusqu'au {{ $sub->trial_ends_at->format('d/m/Y') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 font-semibold text-slate-900">{{ number_format($sub->amount, 0, ',', ' ') }} {{ $sub->currency }}</td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                            @if($sub->status === 'active') bg-emerald-50 text-emerald-700
                                            @elseif($sub->status === 'trial') bg-indigo-50 text-indigo-750
                                            @else bg-red-50 text-red-700 @endif">
                                            {{ $sub->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucun abonnement trouvé.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payments --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Transactions de paiement</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Date / Référence</th>
                                <th class="py-2">Méthode</th>
                                <th class="py-2">Montant</th>
                                <th class="py-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->subscriptionPayments as $pay)
                                <tr>
                                    <td class="py-3">
                                        <p class="font-semibold text-slate-800">{{ $pay->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-[10px] text-slate-400 font-mono">{{ $pay->reference ?? 'N/A' }}</p>
                                    </td>
                                    <td class="py-3 text-slate-650 capitalize">{{ $pay->payment_method ?? 'N/A' }}</td>
                                    <td class="py-3 font-semibold text-slate-900">{{ number_format($pay->amount, 0, ',', ' ') }} {{ $pay->currency }}</td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold
                                            @if($pay->status === 'paid') bg-emerald-50 text-emerald-700
                                            @elseif($pay->status === 'pending') bg-amber-50 text-amber-705
                                            @else bg-red-50 text-red-700 @endif">
                                            {{ $pay->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucun paiement enregistré.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Backups --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Historique des sauvegardes</h3>
                    <a href="{{ route('landlord.backups.index', ['tenant_id' => $tenant->id]) }}" class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800">
                        Voir toutes les sauvegardes
                    </a>
                </div>

                @if($tenant->provisioning_status === 'prepared')
                    <div class="mb-4 p-4 bg-amber-50 border border-amber-100 text-amber-800 rounded-xl text-xs">
                        <p class="font-semibold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            La base de données de cette boutique n’est pas encore active. La sauvegarde réelle sera disponible après provisionnement.
                        </p>
                    </div>
                @else
                    {{-- Action Card --}}
                    <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <p class="text-xs font-semibold text-slate-900">Sauvegardes de bases de données</p>
                            @php
                                $lastTenantBackup = $tenant->backups()->where('status', 'completed')->latest('finished_at')->first();
                            @endphp
                            @if($lastTenantBackup)
                                <p class="text-[11px] text-slate-500">
                                    Dernière sauvegarde : <span class="font-bold font-mono text-slate-700">{{ $lastTenantBackup->filename }}</span> ({{ $lastTenantBackup->sizeForHumans() }}) le {{ $lastTenantBackup->finished_at->format('d/m/Y H:i') }}
                                </p>
                            @else
                                <p class="text-[11px] text-slate-400 font-medium">Aucune sauvegarde complétée pour cette boutique.</p>
                            @endif
                        </div>
                        <div>
                            @if(config('platform.backups.allow_manual_backup', true))
                                <form action="{{ route('landlord.tenants.backups.store', $tenant) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-indigo-650 hover:bg-indigo-750 text-xs font-bold text-white rounded-xl shadow-md shadow-indigo-650/10 transition select-none flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Lancer une sauvegarde
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Fichier</th>
                                <th class="py-2">Type</th>
                                <th class="py-2">Taille</th>
                                <th class="py-2">Date de création</th>
                                <th class="py-2 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->backups()->latest()->limit(5)->get() as $backup)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3 font-mono text-[10px] text-slate-750 select-all">
                                        <a href="{{ route('landlord.backups.show', $backup) }}" class="hover:underline font-bold text-indigo-650">
                                            {{ $backup->filename }}
                                        </a>
                                    </td>
                                    <td class="py-3 text-slate-650">{{ $backup->backupTypeLabel() }}</td>
                                    <td class="py-3 font-semibold text-slate-800">{{ $backup->sizeForHumans() }}</td>
                                    <td class="py-3 text-slate-600">{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 text-right">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $backup->statusBadgeClass() }}">
                                            {{ $backup->statusLabel() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-450">Aucune sauvegarde trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Audit Logs --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Actions Super Admin (Audit)</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-150 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-2">Action</th>
                                <th class="py-2">Description</th>
                                <th class="py-2">Exécuté par</th>
                                <th class="py-2 text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($tenant->auditLogs as $log)
                                <tr>
                                    <td class="py-3 font-semibold text-indigo-700">{{ $log->action }}</td>
                                    <td class="py-3 text-slate-650">{{ $log->description }}</td>
                                    <td class="py-3 text-slate-700 font-medium">{{ $log->landlordUser?->name ?? 'N/A' }}</td>
                                    <td class="py-3 text-right text-slate-450">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-400">Aucun log d'audit pour cette boutique.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function copyAccessMessage() {
        const copyText = document.getElementById("accessMessageText");
        if (copyText) {
            copyText.select();
            copyText.setSelectionRange(0, 99999); /* For mobile devices */
            navigator.clipboard.writeText(copyText.value);
            
            // Show alert or visual feedback
            alert("Message d'accès copié dans le presse-papier !");
        }
    }
</script>
@endpush
