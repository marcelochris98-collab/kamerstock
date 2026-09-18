<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Product;
use App\Models\Notification;
use App\Models\ClientMessage;
use Illuminate\Support\Facades\Auth;

class HeaderComposer
{
    public function compose(View $view): void
    {
        $userId = Auth::id();

        $stockAlertCount = 0;
        $stockAlertProducts = collect();
        if ($userId) {
            $stockAlertQuery = Product::query()
                ->where('is_active', true)
                ->whereRaw('quantity <= alert_threshold');

            $stockAlertCount = (clone $stockAlertQuery)->count();
            $stockAlertProducts = (clone $stockAlertQuery)->limit(5)->get();
        }

        $unreadNotifications = collect();
        $unreadNotificationsCount = 0;
        if ($userId) {
            $unreadNotificationsQuery = Notification::query()
                ->where('user_id', $userId)
                ->where('is_read', false);

            $unreadNotificationsCount = (clone $unreadNotificationsQuery)->count();
            $unreadNotifications = (clone $unreadNotificationsQuery)->latest()->limit(5)->get();
        }

        $totalAlertCount = $stockAlertCount + $unreadNotificationsCount;

        $unreadCrmMessagesCount = 0;
        if ($userId && Auth::user()->hasPermission('crm.messages')) {
            $unreadCrmMessagesCount = ClientMessage::query()
                ->whereNotNull('client_id')
                ->whereNull('user_id')
                ->whereNull('read_at')
                ->count();
        }

        $view->with([
            'stockAlertCount' => $stockAlertCount,
            'stockAlertProducts' => $stockAlertProducts,
            'unreadNotifications' => $unreadNotifications,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'totalAlertCount' => $totalAlertCount,
            'unreadCrmMessagesCount' => $unreadCrmMessagesCount,
        ]);
    }
}
