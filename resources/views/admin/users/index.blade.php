@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Utilisateurs')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-lg text-xs font-medium text-emerald-700">
    {{ session('success') }}
</div>
@endif

@if($errors->has('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-100 rounded-lg text-xs text-red-600">
    {{ $errors->first('error') }}
</div>
@endif

<div class="grid grid-cols-3 gap-6">

    {{-- Formulaire création --}}
    <div class="col-span-1">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Nouvel utilisateur</h2>

            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="christo"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        placeholder="jean@kamerstock.cm"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('email') border-red-300 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Role</label>
                    <select name="role_id"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('role_id') border-red-300 @enderror">
                        <option value="">-- Choisir un role --</option>
                        @foreach($roles as $role)
                            @if($role->slug !== 'admin')
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                            @endif
                        @endforeach
                    </select>
                    @error('role_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Mot de passe</label>
                    <input type="password" name="password"
                        placeholder="Min. 8 caractères"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('password') border-red-300 @enderror">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation"
                        placeholder="Répétez le mot de passe"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>

                <button type="submit"
                    class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Créer l'utilisateur
                </button>
            </form>
        </div>
    </div>

    {{-- Liste utilisateurs --}}
    <div class="col-span-2">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                <p class="text-sm font-semibold text-slate-800">Tous les utilisateurs</p>
                <span class="text-xs text-slate-400">{{ $users->count() }} utilisateur(s)</span>
            </div>

            @if($users->isEmpty())
            <div class="py-12 text-center text-xs text-slate-400">Aucun utilisateur créé.</div>
            @else
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-50 bg-slate-50">
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Utilisateur</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Role</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400">Créé le</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition last:border-0">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-600">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-slate-800">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs font-medium">
                                {{ $user->role?->name ?? 'Sans role' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-400">
                            {{ $user->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">

                                {{-- Activer / Désactiver --}}
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-amber-500 hover:border-amber-200 transition">
                                        @if($user->is_active)
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        @else
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Modifier --}}
                                <button onclick="openEdit({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role_id }}')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-blue-500 hover:border-blue-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>

                                {{-- Supprimer --}}
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Supprimer {{ $user->name }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

{{-- Modal modifier --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-sm font-semibold text-slate-800 mb-4">Modifier l'utilisateur</h3>
        <form method="POST" id="editForm">
            @csrf @method('PUT')

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Nom complet</label>
                <input type="text" name="name" id="editName"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                <input type="email" name="email" id="editEmail"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">Role</label>
                <select name="role_id" id="editRole"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                    @foreach($roles as $role)
                        @if($role->slug !== 'admin')
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-slate-600 mb-1">
                    Nouveau mot de passe
                    <span class="text-slate-400 font-normal">(laisser vide pour ne pas changer)</span>
                </label>
                <input type="password" name="password" placeholder="Min. 8 caractères"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="mb-5">
                <label class="block text-xs font-medium text-slate-600 mb-1">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation"
                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closeEdit()"
                    class="flex-1 py-2 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                    class="flex-1 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
                    Sauvegarder
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEdit(id, name, email, roleId) {
    document.getElementById('editName').value  = name;
    document.getElementById('editEmail').value = email;
    document.getElementById('editRole').value  = roleId;
    document.getElementById('editForm').action = `/admin/users/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}
function closeEdit() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}
</script>
@endpush
