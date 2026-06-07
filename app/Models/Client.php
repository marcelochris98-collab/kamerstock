<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'type',
        'credit_limit',
        'loyalty_score',
        'loyalty_status',
        'risk_level',
        'recommended_credit_limit',
        'credit_used',
        'credit_available',
        'credit_blocked',
        'credit_block_reason',
        'last_score_calculated_at',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'recommended_credit_limit' => 'decimal:2',
        'credit_used' => 'decimal:2',
        'credit_available' => 'decimal:2',
        'credit_blocked' => 'boolean',
        'last_score_calculated_at' => 'datetime',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function creditSales()
    {
        return $this->hasMany(CreditSale::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'particulier' => 'Particulier',
            'entreprise'  => 'Entreprise',
            'revendeur'   => 'Revendeur',
            default       => $this->type,
        };
    }
}
