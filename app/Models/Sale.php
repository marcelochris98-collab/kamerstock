<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_id', 'cash_session_id',
        'total_amount', 'amount_paid', 'change_due', 'discount',
        'payment_mode', 'status', 'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'change_due'   => 'decimal:2',
        'discount'     => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function getPaymentModeLabelAttribute(): string
    {
        return match($this->payment_mode) {
            'cash'         => 'Espèces',
            'orange_money' => 'Orange Money',
            'mtn_money'    => 'MTN Money',
            'cheque'       => 'Chèque',
            'credit'       => 'Crédit',
            'mixte'        => 'Mixte',
            default        => $this->payment_mode,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'completee' => 'Complétée',
            'credit'    => ' Crédit',
            'annulee'   => ' Annulée',
            default     => $this->status,
        };
    }
}