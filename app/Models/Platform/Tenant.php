<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROVISIONING = 'provisioning';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SUSPENDED = 'suspended';

    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];
}
