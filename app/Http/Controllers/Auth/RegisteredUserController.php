<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use App\Services\Tenancy\TenantProvisioningException;
use App\Services\Tenancy\TenantSecurityService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, TenantSecurityService $securityService): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $baseSlug = substr(Str::slug($validated['company_name']), 0, 100);
        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'company_name' => 'Le nom de l’entreprise ne permet pas de créer son identifiant.',
            ]);
        }

        $slug = $baseSlug;
        $suffix = 2;
        while (Tenant::on('landlord')->where('slug', $slug)->exists()) {
            $tail = '-'.$suffix++;
            $slug = substr($baseSlug, 0, 100 - strlen($tail)).$tail;
        }

        $tenant = Tenant::create([
            'name' => $validated['company_name'],
            'slug' => $slug,
            'status' => Tenant::STATUS_PROVISIONING,
        ]);

        try {
            $user = app(TenantProvisioner::class)->provision($tenant, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
        } catch (TenantProvisioningException $exception) {
            throw ValidationException::withMessages([
                'company_name' => $exception->getMessage(),
            ]);
        }

        $request->session()->put('current_tenant_slug', $tenant->slug);
        Auth::login($user);
        $securityService->bindAuthenticatedUser($request, $tenant, $user);
        event(new Registered($user));

        return redirect(route('dashboard', absolute: false))
            ->with('tenant_slug', $tenant->slug);
    }
}
