<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notifyUser(
        ?int $userId,
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'data' => $data,
        ]);
    }

    public function notifyManagers(
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null
    ): void {
        $users = User::query()->get();

        foreach ($users as $user) {
            if (
                method_exists($user, 'hasPermission')
                && (
                    $user->hasPermission('dashboard.view')
                    || $user->hasPermission('credits.view')
                    || $user->hasPermission('settings.manage')
                )
            ) {
                $this->notifyUser($user->id, $type, $title, $message, $link, $data);
            }
        }
    }
}