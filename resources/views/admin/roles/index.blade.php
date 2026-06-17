@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page-title', 'Roles & Permissions')

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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Formulaire création role --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Nouveau role</h2>
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nom du role</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        placeholder="Ex: Superviseur"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400 @error('name') border-red-300 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                        placeholder="Description du role"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                </div>
                <button type="submit"
                    class="w-full py-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Créer le role
                </button>
            </form>
        </div>

        {{-- Liste des permissions disponibles --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-3">Permissions disponibles</h2>
            <div class="space-y-3">
                @foreach($modules as $module => $perms)
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">{{ $module }}</p>
                    <div class="space-y-1">
                        @foreach($perms as $perm)
                        <div class="flex items-center gap-2 px-2 py-1 bg-slate-50 rounded text-xs text-slate-600">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                            {{ $perm->name }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Liste roles --}}
    <div class="lg:col-span-2 space-y-4">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $role->name }}</p>
                        <p class="text-xs text-slate-400">{{ $role->description }} — {{ $role->users_count }} utilisateur(s)</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($role->slug !== 'admin')
                    <button onclick="openPermissions({{ $role->id }}, '{{ $role->name }}')"
                        class="px-3 py-1.5 text-xs font-medium border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition">
                        Gérer permissions
                    </button>
                    @if($role->users_count === 0)
                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                        onsubmit="return confirm('Supprimer le role {{ $role->name }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:text-red-500 hover:border-red-200 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                    @else
                    <span class="px-2 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded">
                        Acces complet
                    </span>
                    @endif
                </div>
            </div>

            {{-- Permissions du role --}}
            <div class="px-5 py-3">
                @if($role->slug === 'admin')
                <p class="text-xs text-slate-400">L'administrateur a acces a toutes les permissions du systeme.</p>
                @elseif($role->permissions->isEmpty())
                <p class="text-xs text-slate-400">Aucune permission assignée — cliquez sur "Gérer permissions".</p>
                @else
                <div class="flex flex-wrap gap-1.5">
                    @foreach($role->permissions as $perm)
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded">
                        {{ $perm->name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Modal permissions --}}
<div id="permModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4 max-h-screen overflow-y-auto">
        <h3 class="text-sm font-semibold text-slate-800 mb-1">Permissions</h3>
        <p class="text-xs text-slate-400 mb-5" id="permRoleName"></p>

        <form method="POST" id="permForm">
            @csrf @method('POST')

            <div class="space-y-4 mb-6">
                @foreach($modules as $module => $perms)
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">{{ $module }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($perms as $perm)
                        <label class="flex items-center gap-2 p-2 border border-slate-100 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                class="perm-checkbox w-3.5 h-3.5 accent-slate-800"
                                data-perm-id="{{ $perm->id }}">
                            <span class="text-xs text-slate-700">{{ $perm->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="closePermissions()"
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
const rolesData = @json($roles->keyBy('id'));

function openPermissions(roleId, roleName) {
    document.getElementById('permRoleName').textContent = 'Role : ' + roleName;
    document.getElementById('permForm').action = `/admin/roles/${roleId}/permissions`;

    // Reset checkboxes
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);

    // Cocher les permissions existantes
    const role = rolesData[roleId];
    if (role && role.permissions) {
        role.permissions.forEach(perm => {
            const cb = document.querySelector(`input[data-perm-id="${perm.id}"]`);
            if (cb) cb.checked = true;
        });
    }

    document.getElementById('permModal').classList.remove('hidden');
    document.getElementById('permModal').classList.add('flex');
}

function closePermissions() {
    document.getElementById('permModal').classList.add('hidden');
    document.getElementById('permModal').classList.remove('flex');
}
</script>
@endpush
