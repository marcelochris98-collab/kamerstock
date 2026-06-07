<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $fillable = [
        'credit_sale_id',
        'client_id',
        'user_id',
        'amount',
        'payment_method',
        'internal_reference',
        'external_reference',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function creditSale()
    {
        return $this->belongsTo(CreditSale::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
