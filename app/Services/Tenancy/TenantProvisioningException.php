<?php

namespace App\Services\Tenancy;

use RuntimeException;
use Throwable;

class TenantProvisioningException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
