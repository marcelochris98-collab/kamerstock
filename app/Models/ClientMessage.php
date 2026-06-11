<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMessage extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'subject',
        'message',
        'type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}