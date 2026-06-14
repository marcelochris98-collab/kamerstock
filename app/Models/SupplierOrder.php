<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'user_id',
        'reference',
        'total_amount',
        'status', // 'brouillon', 'commande', 'recu_partiel', 'recu_complet', 'annule'
        'order_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierOrderItem::class);
    }

    public function receptions()
    {
        return $this->hasMany(SupplierReception::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'brouillon' => 'Brouillon',
            'commande' => 'Commandé',
            'recu_partiel' => 'Reçu Partiel',
            'recu_complet' => 'Reçu Complet',
            'annule' => 'Annulé',
            default => $this->status,
        };
    }
}
