<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierHistory extends Model
{
    protected $fillable = [
        'supplier_id',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}