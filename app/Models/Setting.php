<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'shop_name', 'address', 'phone', 'email',
        'currency', 'tax_rate', 'logo', 'invoice_prefix',
        'business_type', 'business_type_custom',
        'setup_completed', 'setup_completed_at', 'enabled_units', 'setup_step'
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'setup_completed' => 'boolean',
        'enabled_units' => 'array',
        'setup_completed_at' => 'datetime',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::first();
        return $setting?->$key ?? $default;
    }
}