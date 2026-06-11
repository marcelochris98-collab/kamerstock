<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientMessage;
use App\Models\Sale;
use App\Models\CreditSale;
use App\Models\CreditPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        ], [
            'phone.required' => 'Entrez votre numéro de téléphone.',
            'pin.required' => 'Entrez votre code PIN.',
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
        session()->forget([
            'portal_client_id',
            'portal_client_name',
        ]);

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

        $messages = ClientMessage::where('client_id', $client->id)
            ->latest()
            ->paginate(20);

        ClientMessage::where('client_id', $client->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('client-portal.messages', compact('client', 'messages'));
    }

    public function enable(Client $client)
    {
        if (!auth()->check()) {
            abort(403);
        }

        if (!auth()->user()->hasPermission('clients.manage')) {
            abort(403);
        }

        $pin = (string) random_int(1000, 9999);

        $client->forceFill([
            'portal_enabled' => true,
            'portal_pin' => Hash::make($pin),
        ])->save();

        ClientMessage::create([
            'client_id' => $client->id,
            'user_id' => auth()->id(),
            'subject' => 'Accès portail client',
            'message' => 'Votre portail client KamerStock est maintenant actif.',
            'type' => 'system',
        ]);

        return back()->with('success', "Portail activé pour {$client->name}. Code PIN : {$pin}");
    }

    public function disable(Client $client)
    {
        if (!auth()->check()) {
            abort(403);
        }

        if (!auth()->user()->hasPermission('clients.manage')) {
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
}