<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'landlord';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];
}
