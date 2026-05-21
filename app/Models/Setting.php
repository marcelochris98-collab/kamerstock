<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'shop_name', 'address', 'phone', 'email',
        'currency', 'tax_rate', 'logo', 'invoice_prefix'
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = static::first();
        return $setting?->$key ?? $default;
    }
}