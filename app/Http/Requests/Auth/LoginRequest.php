<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Platform\Tenant;
use App\Models\User;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $tenantSlug = $this->input('tenant');
        $email = $this->string('email');

        // ✅ AUTO-DÉTECTION TENANT : Si pas de slug dans la requête, chercher si l'email correspond à un tenant
        if (!$tenantSlug && !empty($email)) {
            $tenantCandidate = Tenant::on('landlord')
                ->where(function ($q) use ($email) {
                    $q->where('owner_login_email', $email)
                      ->orWhere('owner_email', $email);
                })
                ->where('provisioning_status', 'migrated')
                ->first();

            if ($tenantCandidate) {
                $tenantSlug = $tenantCandidate->slug;
            }
        }

        // ✅ CAS TENANT : authentification manuelle sur la DB tenant
        if ($tenantSlug) {
            $tenant = Tenant::on('landlord')
                ->where('slug', $tenantSlug)
                ->where('provisioning_status', 'migrated')
                ->first();

            if ($tenant) {
                app(\App\Services\Tenancy\TenantDatabaseManager::class)->switchToTenant($tenant);

                // Chercher l'utilisateur dans la DB tenant
                $user = User::on('tenant')
                    ->where('email', $email)
                    ->first();

                if ($user && $user->is_active && Hash::check($this->string('password'), $user->password)) {
                    // Connecter l'utilisateur manuellement
                    Auth::login($user, $this->boolean('remember'));
                    
                    // Sauvegarder le slug tenant en session
                    session(['current_tenant_slug' => $tenant->slug]);

                    RateLimiter::clear($this->throttleKey());
                    return;
                }

                // Identifiants incorrects
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
        }

        // CAS NORMAL (boutique legacy / sans tenant) : Auth::attempt() standard sur DB par défaut
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Si Auth::attempt échoue sur la DB centrale, faire une recherche de secours sur tous les tenants migrés
            $allMigratedTenants = Tenant::on('landlord')
                ->where('provisioning_status', 'migrated')
                ->get();

            $dbManager = app(\App\Services\Tenancy\TenantDatabaseManager::class);

            foreach ($allMigratedTenants as $t) {
                try {
                    $dbManager->switchToTenant($t);

                    $tenantUser = User::on('tenant')->where('email', $email)->first();
                    if ($tenantUser && $tenantUser->is_active && Hash::check($this->string('password'), $tenantUser->password)) {
                        Auth::login($tenantUser, $this->boolean('remember'));
                        session(['current_tenant_slug' => $t->slug]);
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                } catch (\Throwable $ex) {
                    // Ignorer les erreurs de connexion temporaires
                }
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}