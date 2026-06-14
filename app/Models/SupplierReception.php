<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReception extends Model
{
    protected $table = 'supplier_receptions';

    protected $fillable = [
        'supplier_order_id',
        'user_id',
        'reference',
        'reception_date',
        'notes',
    ];

    protected $casts = [
        'reception_date' => 'date',
    ];

    public function supplierOrder()
    {
        return $this->belongsTo(SupplierOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierReceptionItem::class);
    }
}
