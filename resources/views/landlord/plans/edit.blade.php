@extends('layouts.landlord')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800" x-data="planForm()">
    
    {{-- Header --}}
    <div class="mb-6">
        <p class="text-xs text-slate-400 font-medium">Plans d'Abonnement / Modifier</p>
        <h1 class="text-xl font-bold text-slate-900 mt-1">Modifier le Plan</h1>
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

    <form action="{{ route('landlord.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Fields --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Plan Details Card --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">1</span>
                        <h2 class="text-sm font-bold text-slate-800">Détails du Plan</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Nom du plan <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="name" @input="updateSlug()" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Slug unique <span class="text-red-500">*</span></label>
                            <input type="text" name="slug" x-model="slug" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Description</label>
                            <textarea name="description" rows="2"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ $plan->description }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Pricing Card --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">2</span>
                        <h2 class="text-sm font-bold text-slate-800">Tarification</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Prix mensuel <span class="text-red-500">*</span></label>
                            <input type="number" name="price_monthly" step="0.01" value="{{ $plan->price_monthly }}" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Prix annuel</label>
                            <input type="number" name="price_yearly" step="0.01" value="{{ $plan->price_yearly }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Devise <span class="text-red-500">*</span></label>
                            <input type="text" name="currency" value="{{ $plan->currency }}" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Limits Card --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">3</span>
                        <h2 class="text-sm font-bold text-slate-800">Limites Techniques (Laisser vide pour illimité)</h2>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Utilisateurs</label>
                            <input type="number" name="max_users" value="{{ $plan->max_users }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Produits</label>
                            <input type="number" name="max_products" value="{{ $plan->max_products }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Clients</label>
                            <input type="number" name="max_clients" value="{{ $plan->max_clients }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-655 mb-1.5">Stockage (Mo)</label>
                            <input type="number" name="max_storage_mb" value="{{ $plan->max_storage_mb }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Branches</label>
                            <input type="number" name="max_branches" value="{{ $plan->max_branches }}"
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Features Card --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <div class="flex items-center gap-2 pb-4 mb-5 border-b border-slate-100">
                        <span class="w-6 h-6 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold text-xs">4</span>
                        <h2 class="text-sm font-bold text-slate-800">Fonctionnalités incluses</h2>
                    </div>
                    <p class="text-xs text-slate-400 mb-3">Saisissez une fonctionnalité par ligne.</p>
                    <textarea name="features_text" rows="5"
                        class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ $featuresText }}</textarea>
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                
                {{-- Visibility & Order --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-4 pb-2 border-b border-slate-100">Paramètres d'Affichage</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Statut</label>
                            <select name="is_active" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                <option value="1" {{ $plan->is_active ? 'selected' : '' }}>Actif (Visible)</option>
                                <option value="0" {{ !$plan->is_active ? 'selected' : '' }}>Inactif (Masqué)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-655 mb-1.5">Ordre d'affichage</label>
                            <input type="number" name="sort_order" value="{{ $plan->sort_order }}" required
                                class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2 text-xs text-slate-900 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                {{-- Action --}}
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-xs font-bold text-white rounded-xl shadow-md shadow-indigo-650/10 transition">
                        Enregistrer modifications
                    </button>
                    <a href="{{ route('landlord.plans.index') }}" class="block text-center text-xs font-semibold text-slate-400 hover:text-slate-600 mt-3 select-none">
                        Annuler
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>

@push('scripts')
<script>
    function planForm() {
        return {
            name: '{{ $plan->name }}',
            slug: '{{ $plan->slug }}',
            
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
