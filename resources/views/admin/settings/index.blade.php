@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen text-slate-800">

    {{-- Header --}}
    <div class="mb-6">
        <p class="text-xs text-slate-400 font-medium">Administration / Paramètres</p>
        <h1 class="text-xl font-bold text-slate-900 mt-1">Configuration de la Quincaillerie</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-lg text-xs font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-3xl bg-white border border-slate-200 shadow-sm rounded-xl p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Informations Générales --}}
                <div class="md:col-span-2">
                    <h2 class="text-sm font-semibold text-slate-800 mb-4">Informations Générales</h2>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nom de la boutique <span class="text-red-500">*</span></label>
                    <input type="text" name="shop_name" value="{{ old('shop_name', $settings->shop_name) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('shop_name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address', $settings->address) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('address') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $settings->phone) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('phone') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $settings->email) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('email') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Zone Upload Logo --}}
<div class="md:col-span-2">
    <label class="block text-xs font-semibold text-slate-600 mb-1">Logo de la quincaillerie</label>
    <div class="flex items-center gap-4 mt-2">
        @if($settings->logo)
            {{-- ICI : On utilise directement la variable car elle contient déjà "logos/..." --}}
            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
        @else
            <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-bold">
                No Lgo
            </div>
        @endif
        <input type="file" name="logo"
            class="text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-slate-200 file:text-xs file:font-semibold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100 cursor-pointer">
    </div>
    @error('logo') <span class="text-red-500 text-[10px] block mt-1">{{ $message }}</span> @enderror
</div>

                {{-- Paramètres Système & Comptables --}}
                <div class="md:col-span-2 mt-4">
                    <h2 class="text-sm font-semibold text-slate-800 mb-4">Système & Comptabilité</h2>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Devise <span class="text-red-500">*</span></label>
                    <input type="text" name="currency" value="{{ old('currency', $settings->currency) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('currency') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Taux de TVA (%) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $settings->tax_rate) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('tax_rate') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Préfixe des Factures <span class="text-red-500">*</span></label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}"
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-900 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    @error('invoice_prefix') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            {{-- Bouton de validation --}}
            <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit"  class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
