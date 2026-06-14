<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Accès non autorisé.');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect($notification->link ?? route('notifications.index'));
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Toutes vos notifications ont été marquées comme lues.');
    }

    public function poll(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Vérifier si les notifications sont activées pour cet utilisateur
        if (isset($user->notifications_enabled) && !$user->notifications_enabled) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
                'timestamp' => now()->toIso8601String()
            ]);
        }

        $lastChecked = $request->query('last_checked');

        $query = Notification::where('user_id', $user->id)
            ->where('is_read', false);

        if ($lastChecked) {
            $query->where('created_at', '>', $lastChecked);
        }

        $newNotifications = $query->latest()->get();

        $volume = isset($user->sound_volume) ? $user->sound_volume / 100 : 0.3;
        $soundsEnabled = isset($user->sounds_enabled) ? $user->sounds_enabled : true;

        return response()->json([
            'notifications' => $newNotifications->map(function ($n) use ($soundsEnabled, $volume) {
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'link' => $n->link ? route('notifications.read', $n) : null,
                    'type' => $n->type,
                    'sound' => $soundsEnabled ? $this->getSoundForNotification($n) : null,
                    'volume' => $volume,
                ];
            }),
            'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function getSoundForNotification(Notification $notification): string
    {
        $type = $notification->type;
        if (in_array($type, ['stock_low', 'stock_empty', 'credit_overdue', 'cash_error'])) {
            return 'alerte';
        }
        if (in_array($type, ['message_sent', 'action_success'])) {
            return 'envoi';
        }
        if (in_array($type, ['crm_message_received', 'crm_reply_received', 'chat_message'])) {
            return 'reception';
        }
        return 'notification';
    }
}
