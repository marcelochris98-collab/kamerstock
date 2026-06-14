<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Client;

class NotificationService
{
    public function notifyUser(
        ?int $userId,
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null,
        string $category = 'messaging'
    ): ?Notification {
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                // Vérifier si les notifications sont désactivées pour l'utilisateur
                if (isset($user->notifications_enabled) && !$user->notifications_enabled) {
                    return null;
                }
                // Vérifier si la catégorie spécifique est désactivée
                if (isset($user->notification_categories) && is_array($user->notification_categories)) {
                    if (!in_array($category, $user->notification_categories)) {
                        return null;
                    }
                }
            }
        }

        $allData = array_merge($data ?? [], ['category' => $category]);

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'data' => $allData,
        ]);
    }

    public function notifyClient(
        int $clientId,
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null,
        string $category = 'messaging'
    ): ?Notification {
        $client = Client::find($clientId);
        if ($client) {
            if (isset($client->notifications_enabled) && !$client->notifications_enabled) {
                return null;
            }
        }

        $allData = array_merge($data ?? [], ['category' => $category]);

        return Notification::create([
            'client_id' => $clientId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'data' => $allData,
        ]);
    }

    public function notifyByPermission(
        string $permission,
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null,
        string $category = 'messaging'
    ): void {
        $users = User::all();
        foreach ($users as $user) {
            if ($user->hasPermission($permission)) {
                $this->notifyUser($user->id, $type, $title, $message, $link, $data, $category);
            }
        }
    }

    public function notifyByPermissions(
        array $permissions,
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null,
        string $category = 'messaging'
    ): void {
        $users = User::all();
        foreach ($users as $user) {
            foreach ($permissions as $perm) {
                if ($user->hasPermission($perm)) {
                    $this->notifyUser($user->id, $type, $title, $message, $link, $data, $category);
                    break; // Notifier l'utilisateur une seule fois
                }
            }
        }
    }

    public function notifyManagers(
        string $type,
        string $title,
        ?string $message = null,
        ?string $link = null,
        ?array $data = null
    ): void {
        // Rétrocompatibilité : Notifie les utilisateurs avec permission dashboard, clients ou settings.
        $this->notifyByPermissions(
            ['dashboard.view', 'clients.view', 'settings.manage'],
            $type,
            $title,
            $message,
            $link,
            $data,
            'messaging'
        );
    }
}