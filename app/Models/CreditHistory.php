<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    protected $fillable = [
        'credit_sale_id',
        'user_id',
        'action',
        'title',
        'description',
        'amount',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function creditSale()
    {
        return $this->belongsTo(CreditSale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
