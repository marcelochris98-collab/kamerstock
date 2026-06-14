<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'reference',
        'type', // 'devis', 'proforma'
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status', // 'brouillon', 'envoye', 'valide', 'rejete', 'converti'
        'valid_until',
        'converted_sale_id',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(QuoteDetail::class);
    }

    public function convertedSale()
    {
        return $this->belongsTo(Sale::class, 'converted_sale_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'devis' => 'Devis',
            'proforma' => 'Facture Proforma',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'brouillon' => 'Brouillon',
            'envoye' => 'Envoyé',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            'converti' => 'Converti en Vente',
            default => $this->status,
        };
    }
}
