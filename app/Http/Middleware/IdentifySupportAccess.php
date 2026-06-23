<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Platform\SupportAccess;
use App\Services\Platform\SupportContext;
use App\Services\Tenancy\TenantContext;
use Symfony\Component\HttpFoundation\Response;

class IdentifySupportAccess
{
    protected SupportContext $supportContext;
    protected TenantContext $tenantContext;

    public function __construct(SupportContext $supportContext, TenantContext $tenantContext)
    {
        $this->supportContext = $supportContext;
        $this->tenantContext = $tenantContext;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if support access is requested or stored in session
        $supportAccessId = $request->query('support_access') ?: session('support_access_id');

        if ($supportAccessId) {
            $access = SupportAccess::on('landlord')->find($supportAccessId);

            if ($access) {
                // Check if access is valid
                if (!$access->canBeUsed()) {
                    // Clean session if invalid
                    session()->forget(['support_access_id', 'support_tenant_id']);
                    $this->supportContext->clear();

                    if (auth('landlord')->check()) {
                        return redirect()->route('landlord.support.index')
                            ->with('error', "La session de support a expiré ou a été révoquée.");
                    }

                    abort(403, "Accès support non autorisé, expiré ou révoqué.");
                }

                // Verify it matches the current resolved tenant (if a tenant is resolved)
                if ($this->tenantContext->hasTenant()) {
                    $resolvedTenant = $this->tenantContext->tenant();
                    if ($access->tenant_id !== $resolvedTenant->id) {
                        session()->forget(['support_access_id', 'support_tenant_id']);
                        $this->supportContext->clear();
                        
                        abort(403, "Cet accès support ne correspond pas à la boutique demandée.");
                    }
                }

                // If everything is fine, store in context and ensure session holds it
                $this->supportContext->setAccess($access);
                if (!session()->has('support_access_id')) {
                    session([
                        'support_access_id' => $access->id,
                        'support_tenant_id' => $access->tenant_id,
                    ]);
                }

                // Log in virtually as the first admin or owner for the current request when in tenant context
                if ($this->tenantContext->hasTenant()) {
                    try {
                        $user = \App\Models\User::whereHas('role', function($q) {
                            $q->where('slug', 'admin');
                        })->first() ?: \App\Models\User::first();

                        if ($user) {
                            auth()->setUser($user);
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning('Support Access: Could not set virtual user: ' . $e->getMessage());
                    }
                }
            } else {
                // Access ID provided but not found
                session()->forget(['support_access_id', 'support_tenant_id']);
                $this->supportContext->clear();
            }
        }

        return $next($request);
    }
}
