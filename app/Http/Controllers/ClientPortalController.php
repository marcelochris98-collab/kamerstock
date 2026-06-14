<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientMessage;
use App\Models\Sale;
use App\Models\CreditSale;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientPortalController extends Controller
{
    public function login()
    {
        if (session()->has('portal_client_id')) {
            return redirect()->route('client.portal.dashboard');
        }

        return view('client-portal.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:50',
            'pin' => 'required|string|min:4|max:10',
        ]);

        $cleanPhone = preg_replace('/\D+/', '', $request->phone);

        $client = Client::whereRaw(
            "REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?",
            ["%{$cleanPhone}%"]
        )->first();

        if (!$client || !$client->portal_enabled || !$client->portal_pin) {
            return back()->withInput()->withErrors([
                'phone' => 'Aucun portail client actif trouvé pour ce numéro.',
            ]);
        }

        if (!Hash::check($request->pin, $client->portal_pin)) {
            return back()->withInput()->withErrors([
                'pin' => 'Code PIN incorrect.',
            ]);
        }

        session([
            'portal_client_id' => $client->id,
            'portal_client_name' => $client->name,
        ]);

        $client->forceFill([
            'portal_last_login_at' => now(),
        ])->save();

        return redirect()->route('client.portal.dashboard');
    }

    public function logout()
    {
        session()->forget(['portal_client_id', 'portal_client_name']);

        return redirect()->route('client.portal.login')
            ->with('success', 'Vous êtes déconnecté.');
    }

    public function dashboard()
    {
        $client = $this->currentClient();

        $salesCount = Sale::where('client_id', $client->id)
            ->where('status', '!=', 'annulee')
            ->count();

        $totalPurchases = Sale::where('client_id', $client->id)
            ->where('status', '!=', 'annulee')
            ->sum('total_amount');

        $creditUsed = CreditSale::where('client_id', $client->id)
            ->whereIn('status', ['en_attente', 'partiel', 'en_retard'])
            ->sum('amount_due');

        $creditsCount = CreditSale::where('client_id', $client->id)->count();

        $unreadMessages = ClientMessage::where('client_id', $client->id)
            ->whereNotNull('user_id')
            ->whereNull('read_at')
            ->count();

        $recentSales = Sale::where('client_id', $client->id)
            ->where('status', '!=', 'annulee')
            ->latest()
            ->limit(5)
            ->get();

        $recentCredits = CreditSale::where('client_id', $client->id)
            ->latest()
            ->limit(5)
            ->get();

        $recentMessages = ClientMessage::where('client_id', $client->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('client-portal.dashboard', compact(
            'client',
            'salesCount',
            'totalPurchases',
            'creditUsed',
            'creditsCount',
            'unreadMessages',
            'recentSales',
            'recentCredits',
            'recentMessages'
        ));
    }

    public function sales()
    {
        $client = $this->currentClient();

        $sales = Sale::with(['details.product'])
            ->where('client_id', $client->id)
            ->where('status', '!=', 'annulee')
            ->latest()
            ->paginate(20);

        return view('client-portal.sales', compact('client', 'sales'));
    }

    public function credits()
    {
        $client = $this->currentClient();

        $credits = CreditSale::with(['sale', 'payments'])
            ->where('client_id', $client->id)
            ->latest()
            ->paginate(20);

        $payments = CreditPayment::where('client_id', $client->id)
            ->latest()
            ->limit(30)
            ->get();

        return view('client-portal.credits', compact('client', 'credits', 'payments'));
    }

    public function messages()
    {
        $client = $this->currentClient();

        ClientMessage::where('client_id', $client->id)
            ->whereNotNull('user_id')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = ClientMessage::where('client_id', $client->id)
            ->oldest()
            ->get();

        return view('client-portal.messages', compact('client', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $client = $this->currentClient();

        $request->validate([
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        ClientMessage::create([
            'client_id' => $client->id,
            'user_id' => null,
            'subject' => $request->subject ?: 'Message client',
            'message' => $request->message,
            'type' => 'client',
        ]);

        app(\App\Services\NotificationService::class)->notifyByPermission(
            'crm.messages',
            'crm_message_received',
            'Nouveau message client',
            "Le client {$client->name} a envoyé un message : " . \Illuminate\Support\Str::limit($request->message, 80),
            route('admin.crm_messages.index', ['client_id' => $client->id]),
            ['client_id' => $client->id],
            'messaging'
        );

        return back()->with('success', 'Votre message a été envoyé.');
    }

    public function enable(Client $client)
    {
        return $this->generateAccess($client);
    }

    public function sendAccess(Client $client)
    {
        return $this->generateAccess($client);
    }

    private function generateAccess(Client $client)
    {
        if (!auth()->check() || !auth()->user()->hasPermission('clients.manage')) {
            abort(403);
        }

        $pin = (string) random_int(1000, 9999);
        $portalUrl = route('client.portal.login');

        $client->forceFill([
            'portal_enabled' => true,
            'portal_pin' => Hash::make($pin),
        ])->save();

        $message = "Bonjour {$client->name}, votre espace client KamerStock est actif.\n\n"
            . "Lien : {$portalUrl}\n"
            . "Téléphone : {$client->phone}\n"
            . "Code PIN : {$pin}\n\n"
            . "Vous pouvez consulter vos achats, crédits et messages.";

        ClientMessage::create([
            'client_id' => $client->id,
            'user_id' => auth()->id(),
            'subject' => 'Accès portail client',
            'message' => $message,
            'type' => 'system',
        ]);

        $whatsappPhone = $this->formatPhoneForWhatsapp($client->phone);

        return back()
            ->with('success', "Accès portail généré pour {$client->name}. Code PIN : {$pin}")
            ->with('portal_access', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'phone' => $client->phone,
                'email' => $client->email,
                'pin' => $pin,
                'url' => $portalUrl,
                'message' => $message,
                'whatsapp_url' => $whatsappPhone ? 'https://wa.me/' . $whatsappPhone . '?text=' . urlencode($message) : null,
                'email_url' => $client->email ? 'mailto:' . $client->email . '?subject=' . urlencode('Accès portail KamerStock') . '&body=' . urlencode($message) : null,
            ]);
    }

    public function disable(Client $client)
    {
        if (!auth()->check() || !auth()->user()->hasPermission('clients.manage')) {
            abort(403);
        }

        $client->forceFill([
            'portal_enabled' => false,
            'portal_pin' => null,
        ])->save();

        return back()->with('success', "Portail désactivé pour {$client->name}.");
    }

    private function currentClient(): Client
    {
        $clientId = session('portal_client_id');

        if (!$clientId) {
            redirect()->route('client.portal.login')->send();
        }

        $client = Client::find($clientId);

        if (!$client || !$client->portal_enabled) {
            session()->forget(['portal_client_id', 'portal_client_name']);
            redirect()->route('client.portal.login')->send();
        }

        return $client;
    }

    private function formatPhoneForWhatsapp(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 9 && str_starts_with($digits, '6')) {
            return '237' . $digits;
        }

        return $digits ?: null;
    }

    public function getMessagesJson()
    {
        $client = $this->currentClient();

        ClientMessage::where('client_id', $client->id)
            ->whereNotNull('user_id')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = ClientMessage::where('client_id', $client->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'client_id' => $client->id,
            'messages' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'subject' => $msg->subject,
                    'message' => $msg->message,
                    'type' => $msg->type,
                    'is_from_client' => is_null($msg->user_id),
                    'created_at' => $msg->created_at->format('d/m/Y H:i'),
                ];
            })
        ]);
    }

    public function sendMessageAjax(Request $request)
    {
        $client = $this->currentClient();

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $msg = ClientMessage::create([
            'client_id' => $client->id,
            'user_id' => null,
            'subject' => 'Message client (chat)',
            'message' => $request->message,
            'type' => 'client',
        ]);

        app(\App\Services\NotificationService::class)->notifyByPermission(
            'crm.messages',
            'crm_message_received',
            'Nouveau message client (chat)',
            "Le client {$client->name} a écrit dans le chat : " . \Illuminate\Support\Str::limit($request->message, 80),
            route('admin.crm_messages.index', ['client_id' => $client->id]),
            ['client_id' => $client->id],
            'messaging'
        );

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'subject' => $msg->subject,
                'message' => $msg->message,
                'type' => $msg->type,
                'is_from_client' => true,
                'created_at' => $msg->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $client = $this->currentClient();
        
        $client->forceFill([
            'notifications_enabled' => $request->has('notifications_enabled'),
            'sounds_enabled'        => $request->has('sounds_enabled'),
        ])->save();
        
        return back()->with('success', 'Vos préférences de notification ont été mises à jour.');
    }

    public function pollNotifications(Request $request)
    {
        $client = $this->currentClient();
        
        // Vérifier si les notifications sont activées pour ce client
        if (isset($client->notifications_enabled) && !$client->notifications_enabled) {
            return response()->json([
                'messages' => [],
                'unread_count' => 0,
                'timestamp' => now()->toIso8601String()
            ]);
        }

        $lastChecked = $request->query('last_checked');

        $query = \App\Models\ClientMessage::where('client_id', $client->id)
            ->whereNotNull('user_id')
            ->whereNull('read_at');

        if ($lastChecked) {
            $query->where('created_at', '>', $lastChecked);
        }

        $newMessages = $query->latest()->get();

        $soundsEnabled = isset($client->sounds_enabled) ? $client->sounds_enabled : true;

        return response()->json([
            'messages' => $newMessages->map(function ($msg) use ($soundsEnabled) {
                return [
                    'id' => $msg->id,
                    'title' => 'Nouveau message support',
                    'message' => \Illuminate\Support\Str::limit($msg->message, 80),
                    'sound' => $soundsEnabled ? 'reception' : null,
                ];
            }),
            'unread_count' => \App\Models\ClientMessage::where('client_id', $client->id)
                ->whereNotNull('user_id')
                ->whereNull('read_at')
                ->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}