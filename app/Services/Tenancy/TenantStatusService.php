<?php

namespace App\Services\Tenancy;

use App\Models\Platform\Tenant;

class TenantStatusService
{
    public function canAccess(Tenant $tenant): bool
    {
        return $tenant->status === Tenant::STATUS_ACTIVE;
    }

    public function blockedMessage(Tenant $tenant): string
    {
        return match ($tenant->status) {
            Tenant::STATUS_PENDING, Tenant::STATUS_PROVISIONING => 'Votre espace entreprise est en cours de préparation.',
            Tenant::STATUS_FAILED => 'La création de cet espace entreprise a échoué.',
            Tenant::STATUS_SUSPENDED => 'Cet espace entreprise a été suspendu.',
            default => 'Cet espace entreprise n’est pas disponible.',
        };
    }
}
