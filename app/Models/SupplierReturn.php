<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    protected $table = 'supplier_returns';

    protected $fillable = [
        'supplier_id',
        'purchase_id',
        'user_id',
        'reference',
        'total_amount',
        'status', // 'brouillon', 'valide'
        'return_date',
        'reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'return_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'brouillon' => 'Brouillon',
            'valide' => 'Validé (Stock Décrémenté)',
            default => $this->status,
        };
    }
}
