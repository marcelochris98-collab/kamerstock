@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800" x-data="tenantForm()">
    
    {{-- Header --}}
    <div class="mb-6">
        <p class="text-xs text-slate-400 font-medium">Boutiques / Modifier</p>
        <h1 class="text-xl font-bold text-slate-900 mt-1">Modifier la Boutique</h1>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('landlord.tenants.update', $tenant) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Fields --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Boutique Info --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">1</span>
                        <h2 class="text-sm font-bold text-slate-800">Informations Générales</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Nom de la boutique <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="name" @input="updateSlug()" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Slug URL <span class="text-red-500">*</span></label>
                            <input type="text" name="slug" x-model="slug" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ type: '{{ $tenant->business_type ?? 'quincaillerie' }}' }">
                            <div>
                                <label class="block text-xs font-semibold text-slate-655 mb-1.5">Secteur d'activité</label>
                                <select name="business_type" x-model="type"
                                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
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
                            </div>

                            <div x-show="type === 'autre'" x-cloak>
                                <label class="block text-xs font-semibold text-slate-655 mb-1.5">Secteur personnalisé</label>
                                <input type="text" name="business_type_custom" value="{{ $tenant->business_type_custom }}"
                                    class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Notes internes</label>
                            <textarea name="notes" rows="3"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ $tenant->notes }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Owner Info --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">2</span>
                        <h2 class="text-sm font-bold text-slate-800">Propriétaire & Contact</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Nom complet</label>
                            <input type="text" name="owner_name" value="{{ $tenant->owner_name }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Adresse e-mail</label>
                            <input type="email" name="owner_email" value="{{ $tenant->owner_email }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Téléphone</label>
                            <input type="text" name="owner_phone" value="{{ $tenant->owner_phone }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Regional Settings --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">3</span>
                        <h2 class="text-sm font-bold text-slate-800">Configurations Régionales</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Timezone</label>
                            <input type="text" name="timezone" value="{{ $tenant->timezone ?? 'Africa/Douala' }}" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Devise</label>
                            <input type="text" name="currency" value="{{ $tenant->currency ?? 'FCFA' }}" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Plan & Status --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Abonnement & Statut</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Statut boutique</label>
                            <select name="status" x-model="status" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="trial">Période d'essai</option>
                                <option value="active">Actif</option>
                                <option value="read_only">Lecture seule</option>
                                <option value="suspended">Suspendu</option>
                                <option value="archived">Archivé</option>
                            </select>
                        </div>

                        <div x-show="status === 'trial'">
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Fin de l'essai gratuit</label>
                            <input type="date" name="trial_ends_at" value="{{ $tenant->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d') : '' }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Fin d'abonnement</label>
                            <input type="date" name="subscription_ends_at" value="{{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d') : '' }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-md shadow-indigo-650/10 transition">
                        Enregistrer modifications
                    </button>
                    <a href="{{ route('landlord.tenants.show', $tenant) }}" class="block text-center text-xs font-semibold text-slate-400 hover:text-slate-600 mt-3 select-none">
                        Annuler
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
    function tenantForm() {
        return {
            name: '{{ $tenant->name }}',
            slug: '{{ $tenant->slug }}',
            status: '{{ $tenant->status }}',
            
            updateSlug() {
                this.slug = this.name
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
        };
    }
</script>
@endpush
@endsection
