<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditSale extends Model
{
    protected $fillable = [
        'sale_id',
        'client_id',
        'user_id',
        'total_amount',
        'amount_paid',
        'amount_due',
        'status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments()
    {
        return $this->hasMany(CreditPayment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);

    }
    public function histories()
{
    return $this->hasMany(CreditHistory::class)
        ->latest();
}
}