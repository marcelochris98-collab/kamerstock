<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'address', 'type', 'credit_limit'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
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