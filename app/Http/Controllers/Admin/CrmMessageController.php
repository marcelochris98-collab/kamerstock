<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmMessageController extends Controller
{
    public function index(Request $request)
    {
        $clientsQuery = Client::whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('client_messages')
                ->whereColumn('client_messages.client_id', 'clients.id');
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $clientsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $clientsQuery->get();

        $clients = $clients->map(function ($client) {
            $client->unread_count = ClientMessage::where('client_id', $client->id)
                ->whereNull('user_id')
                ->whereNull('read_at')
                ->count();
            
            $client->last_message = ClientMessage::where('client_id', $client->id)
                ->latest()
                ->first();
                
            return $client;
        })->sortByDesc(function ($client) {
            return $client->last_message ? $client->last_message->created_at : $client->created_at;
        });

        $activeClient = null;
        if ($request->filled('client_id')) {
            $activeClient = Client::find($request->client_id);
        } elseif ($clients->isNotEmpty()) {
            $activeClient = $clients->first();
        }

        $messages = [];
        if ($activeClient) {
            ClientMessage::where('client_id', $activeClient->id)
                ->whereNull('user_id')
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $messages = ClientMessage::where('client_id', $activeClient->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('admin.crm_messages.index', compact('clients', 'activeClient', 'messages'));
    }

    public function history(Client $client)
    {
        ClientMessage::where('client_id', $client->id)
            ->whereNull('user_id')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = ClientMessage::where('client_id', $client->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'client' => $client,
            'messages' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_from_client' => is_null($msg->user_id),
                    'created_at' => $msg->created_at->format('d/m/Y H:i'),
                ];
            })
        ]);
    }

    public function reply(Request $request, Client $client)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        $type = $user->role ? $user->role->slug : 'admin';

        $msg = ClientMessage::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'subject' => 'Réponse support',
            'message' => $request->message,
            'type' => $type,
        ]);

        app(\App\Services\NotificationService::class)->notifyClient(
            $client->id,
            'crm_reply_received',
            'Nouveau message du support',
            "Le support KamerStock a répondu à votre message : " . \Illuminate\Support\Str::limit($request->message, 80),
            route('client.portal.messages'),
            ['message_id' => $msg->id],
            'messaging'
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_from_client' => false,
                    'created_at' => $msg->created_at->format('d/m/Y H:i'),
                ]
            ]);
        }

        return back()->with('success', 'Votre réponse a été envoyée.');
    }
}
