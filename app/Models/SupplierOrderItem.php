<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOrderItem extends Model
{
    protected $table = 'supplier_order_items';

    protected $fillable = [
        'supplier_order_id',
        'product_id',
        'quantity',
        'quantity_received',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_received' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
