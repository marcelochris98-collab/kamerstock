<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles       = Role::with('permissions')->withCount('users')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        $modules     = $permissions->groupBy('module');
        return view('admin.roles.index', compact('roles', 'permissions', 'modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique'   => 'Ce nom de rôle existe déjà.',
        ]);

        $role = Role::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
            'is_active'   => true,
        ]);

        ActivityLog::record('role.create', "Création rôle : {$role->name}");

        return back()->with('success', " Rôle {$role->name} créé !");
    }

    public function update(Request $request, Role $role)
    {
        if ($role->slug === 'admin') {
            return back()->withErrors(['error' => 'Impossible de modifier le rôle Admin.']);
        }

        $request->validate([
            'name'        => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
        ]);

        $role->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'description' => $request->description,
        ]);

        ActivityLog::record('role.update', "Modification rôle : {$role->name}");

        return back()->with('success', " Rôle mis à jour !");
    } 

    public function destroy(Role $role)
    {
        if (in_array($role->slug, ['admin', 'caissier', 'magasinier', 'comptable'])) {
            return back()->withErrors(['error' => 'Impossible de supprimer un rôle système.']);
        }

        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'Impossible de supprimer un rôle assigné à des utilisateurs.']);
        }

        $role->delete();
        ActivityLog::record('role.delete', "Suppression rôle : {$role->name}");

        return back()->with('success', " Rôle supprimé !");
    }

    public function syncPermissions(Request $request, Role $role)
    {
        if ($role->slug === 'admin') {
            return back()->withErrors(['error' => 'Les permissions Admin ne peuvent pas être modifiées.']);
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        ActivityLog::record('role.permissions', "Permissions modifiées pour le rôle : {$role->name}");

        return back()->with('success', " Permissions mises à jour !");
    }
}
