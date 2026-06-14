<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPointsHistory extends Model
{
    protected $table = 'loyalty_points_histories';

    protected $fillable = [
        'client_id',
        'sale_id',
        'points',
        'description',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
