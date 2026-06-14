<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReceptionItem extends Model
{
    protected $table = 'supplier_reception_items';

    protected $fillable = [
        'supplier_reception_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function supplierReception()
    {
        return $this->belongsTo(SupplierReception::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
