<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'contact_person',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function histories()
    {
        return $this->hasMany(SupplierHistory::class);
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->purchases()->sum('total_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->purchases()->sum('amount_paid');
    }

    public function getTotalDueAttribute()
    {
        return $this->purchases()->sum('amount_due');
    }

    public function getPurchasesCountAttribute()
    {
        return $this->purchases()->count();
    }

    public function getLastPurchaseAttribute()
    {
        return $this->purchases()->latest()->first();
    }

    public function getSupplierStatusAttribute()
    {
        $totalPurchases = $this->purchases()->sum('total_amount');
        $count = $this->purchases()->count();

        return match (true) {
            $totalPurchases >= 5000000 || $count >= 20 => 'stratégique',
            $totalPurchases >= 1000000 || $count >= 10 => 'important',
            $totalPurchases > 0 || $count >= 3 => 'régulier',
            default => 'occasionnel',
        };
    }
}