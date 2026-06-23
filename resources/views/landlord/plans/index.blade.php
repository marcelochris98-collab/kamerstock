@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Plans d'Abonnement</h1>
            <p class="text-xs text-slate-400 font-medium mt-1">Tarifs et limites applicables aux boutiques de la plateforme</p>
        </div>
        <div>
            <a href="{{ route('landlord.plans.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-md shadow-indigo-650/10 transition select-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter un Plan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($plans as $plan)
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6 hover:shadow-md transition duration-200 flex flex-col justify-between">
                
                <div>
                    {{-- Header Plan --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-slate-900">{{ $plan->name }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold {{ $plan->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    {{-- Description --}}
                    <p class="text-xs text-slate-450 leading-relaxed mb-4">{{ $plan->description }}</p>

                    {{-- Pricing --}}
                    <div class="mb-5 pb-5 border-b border-slate-100">
                        <p class="text-2xl font-black text-slate-900 leading-none">
                            {{ number_format($plan->price_monthly, 0, ',', ' ') }} <span class="text-xs font-semibold text-slate-450">{{ $plan->currency }}/mois</span>
                        </p>
                        @if($plan->price_yearly)
                            <p class="text-[10px] text-slate-400 font-medium mt-1">Ou {{ number_format($plan->price_yearly, 0, ',', ' ') }} {{ $plan->currency }}/an</p>
                        @endif
                    </div>

                    {{-- Limits --}}
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Utilisateurs max</span>
                            <span class="font-bold text-slate-700">{{ $plan->max_users ?? 'Illimité' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Produits max</span>
                            <span class="font-bold text-slate-700">{{ $plan->max_products ?? 'Illimité' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Clients max</span>
                            <span class="font-bold text-slate-700">{{ $plan->max_clients ?? 'Illimité' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-slate-400">Boutiques / Branches</span>
                            <span class="font-bold text-slate-700">{{ $plan->max_branches ?? 'Illimité' }}</span>
                        </div>
                    </div>

                    {{-- Features List --}}
                    @if(is_array($plan->features) && count($plan->features) > 0)
                        <div class="space-y-2.5 mb-6">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Fonctionnalités</p>
                            @foreach($plan->features as $feat)
                                <div class="flex items-start gap-2 text-xs text-slate-600">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $feat }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action Button --}}
                <div class="pt-4 border-t border-slate-50">
                    <a href="{{ route('landlord.plans.edit', $plan) }}" class="w-full inline-flex items-center justify-center py-2 bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 rounded-xl transition">
                        Modifier le Plan
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white border border-slate-200 rounded-2xl p-8 text-center text-slate-400 text-xs">
                Aucun plan d'abonnement trouvé.
            </div>
        @endforelse
    </div>

</div>
@endsection
