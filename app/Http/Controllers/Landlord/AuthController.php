<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Platform\LandlordAuditLog;

class AuthController extends Controller
{
    /**
     * Show the landlord login form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('landlord')->check()) {
            return redirect()->route('landlord.dashboard');
        }
        return view('landlord.auth.login');
    }

    /**
     * Handle a landlord login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('landlord')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('landlord')->user();

            if (!$user->is_active) {
                Auth::guard('landlord')->logout();
                return back()->withErrors([
                    'email' => 'Ce compte Super Admin est désactivé.',
                ]);
            }

            // Update last login
            $user->update([
                'last_login_at' => now(),
            ]);

            // Log audit
            LandlordAuditLog::log('login', 'Connexion réussie du Super Admin.');

            $request->session()->regenerate();

            return redirect()->intended(route('landlord.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Log the landlord user out of the application.
     */
    public function logout(Request $request)
    {
        if (Auth::guard('landlord')->check()) {
            LandlordAuditLog::log('logout', 'Déconnexion du Super Admin.');
            Auth::guard('landlord')->logout();
        }

        // We invalidate landlord specific session if necessary, but standard session invalidate is safe
        // However, standard $request->session()->invalidate() will log out standard web guard users too.
        // Wait, standard Laravel practice: since web and landlord guards share the same session under different keys,
        // we can log out only the landlord guard, so that if they are also logged in as a boutique admin,
        // they don't get logged out there. But simple session regeneration or invalidation is fine.
        // Let's do a simple logout and redirect, to keep it clean.
        return redirect()->route('landlord.login');
    }
}
