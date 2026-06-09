<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditSetting extends Model
{
    protected $fillable = [
        'min_sales',
        'min_months',
        'min_score',
        'regular_coefficient',
        'loyal_coefficient',
        'premium_coefficient',
        'allow_regular',
        'allow_loyal',
        'allow_premium',
        'allow_high_risk',
        'allow_admin_exception',
        'max_credit_limit',
    ];

    protected $casts = [
        'allow_regular' => 'boolean',
        'allow_loyal' => 'boolean',
        'allow_premium' => 'boolean',
        'allow_high_risk' => 'boolean',
        'allow_admin_exception' => 'boolean',
        'regular_coefficient' => 'decimal:2',
        'loyal_coefficient' => 'decimal:2',
        'premium_coefficient' => 'decimal:2',
        'max_credit_limit' => 'decimal:2',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'min_sales' => 10,
            'min_months' => 2,
            'min_score' => 40,
            'regular_coefficient' => 0.50,
            'loyal_coefficient' => 0.75,
            'premium_coefficient' => 1.00,
            'allow_regular' => true,
            'allow_loyal' => true,
            'allow_premium' => true,
            'allow_high_risk' => false,
            'allow_admin_exception' => true,
            'max_credit_limit' => 2000000,
        ]);
    }
}