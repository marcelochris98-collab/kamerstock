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

        // ✅ CAS TENANT : authentification manuelle sur la DB tenant
        // Auth::attempt() utilise le provider résolu au boot — il ignore le switch
        // de DB fait dans le contrôleur. On contourne en faisant le check manuellement.
        if ($tenantSlug) {

            $tenant = Tenant::on('landlord')
                ->where('slug', $tenantSlug)
                ->where('provisioning_status', 'migrated')
                ->first();

            if ($tenant) {
                // Configurer et activer la connexion tenant
                config(['database.connections.tenant.database' => $tenant->database_name]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
                DB::reconnect('tenant');

                // Chercher l'utilisateur dans la DB tenant
                $user = User::on('tenant')
                    ->where('email', $this->string('email'))
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

        // CAS NORMAL (boutique legacy / sans tenant) : Auth::attempt() standard
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
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