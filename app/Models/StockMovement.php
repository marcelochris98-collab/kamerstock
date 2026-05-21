<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'type', 'quantity',
        'reason', 'reference_type', 'reference_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
      public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'entree'      => ' Entrée',
            'sortie'      => ' Sortie',
           
            default       => $this->type,
    
    };
}
       }