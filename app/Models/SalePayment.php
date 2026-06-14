<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $fillable = [
        'sale_id',
        'payment_mode',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function getPaymentModeLabelAttribute(): string
    {
        return match($this->payment_mode) {
            'cash' => 'Espèces',
            'orange_money' => 'Orange Money',
            'mtn_money' => 'MTN Money',
            'virement' => 'Virement',
            'cheque' => 'Chèque',
            default => $this->payment_mode,
        };
    }
}
