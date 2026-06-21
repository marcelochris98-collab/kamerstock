<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'description', 'ip_address', 'user_agent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $description = ''): void
    {
        static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'description'=> $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if (auth()->check()) {
            app(\App\Services\NotificationService::class)->notifyUser(
                auth()->id(),
                'activity_log',
                'Nouvelle Activité',
                $description,
              \Route::has('audit-logs.index') ? route('audit-logs.index') : route('dashboard'),
                null,
                'system'
            );
        }
    }
}