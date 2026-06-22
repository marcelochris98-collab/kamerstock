<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'supplier_id', 'name', 'reference',
        'unit', 'price_buy', 'price_sell',
        'price_sell_company', 'price_sell_reseller', 'price_sell_wholesale',
        'quantity', 'alert_threshold', 'tax_rate', 'description', 'is_active'
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'price_buy'  => 'decimal:2',
        'price_sell' => 'decimal:2',
        'price_sell_company' => 'decimal:2',
        'price_sell_reseller' => 'decimal:2',
        'price_sell_wholesale' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->alert_threshold;
    }

    public function getUnitLabelAttribute(): string
    {
        return match($this->unit) {
            'piece'   => 'Pièce(s)',
            'metre'   => 'Mètre(s)',
            'kg'      => 'Kg',
            'litre'   => 'Litre(s)',
            'boite'   => 'Boîte(s)',
            'sachet'  => 'Sachet(s)',
            'carton'  => 'Carton(s)',
            'paquet'  => 'Paquet(s)',
            'flacon'  => 'Flacon(s)',
            'tube'    => 'Tube(s)',
            'kit'     => 'Kit(s)',
            'lot'     => 'Lot(s)',
            'palette' => 'Palette(s)',
            'sac'     => 'Sac(s)',
            default   => $this->unit,
        };
    }

    public function getMarginAttribute(): float
    {
        return $this->price_sell - $this->price_buy;
    }

    public function getMarginPercentAttribute(): float
    {
        if ($this->price_buy == 0) return 0;
        return round(($this->margin / $this->price_buy) * 100, 2);
    }

    public function getPriceForType(?string $type): float
    {
        if (!$type) {
            return (float) $this->price_sell;
        }

        $price = match($type) {
            'entreprise' => $this->price_sell_company,
            'revendeur'  => $this->price_sell_reseller,
            'grossiste'  => $this->price_sell_wholesale,
            default      => $this->price_sell,
        };

        return (float) ($price ?: $this->price_sell);
    }
}