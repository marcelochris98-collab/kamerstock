<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')
                     ->where('id', '!=', auth()->id())
                     ->orderBy('created_at', 'desc')
                     ->get();
        $roles = Role::where('is_active', true)->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id'  => 'required|exists:roles,id',
        ], [
            'name.required'     => 'Le nom est obligatoire.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'password.min'      => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
            'role_id.required'  => 'Le rôle est obligatoire.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'is_active' => true,
        ]);

        ActivityLog::record('user.create', "Création utilisateur : {$user->name}");

        app(\App\Services\NotificationService::class)->notifyByPermission(
            'users.manage',
            'user_created',
            'Nouvel utilisateur',
            "L'utilisateur {$user->name} a été créé avec le rôle {$user->role_label}.",
            route('admin.users.index'),
            ['user_id' => $user->id],
            'admin'
        );

        return back()->with('success', "✅ Utilisateur {$user->name} créé avec succès !");
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'password'=> 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::record('user.update', "Modification utilisateur : {$user->name}");

        return back()->with('success', "✅ Utilisateur mis à jour !");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Impossible de supprimer votre propre compte.']);
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('user.delete', "Suppression utilisateur : {$name}");

        return back()->with('success', "✅ Utilisateur supprimé !");
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Impossible de désactiver votre propre compte.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';
        ActivityLog::record('user.toggle', "Compte {$status} : {$user->name}");

        return back()->with('success', " Compte {$status} !");
    }
}