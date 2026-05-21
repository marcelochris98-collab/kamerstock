<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount('sales')
                         ->orderBy('name')
                         ->paginate(20);
        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:150',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:100',
            'type'         => 'required|in:particulier,entreprise,revendeur',
            'credit_limit' => 'nullable|numeric|min:0',
        ], [
            'name.required' => 'Le nom est obligatoire.',
        ]);

        $client = Client::create($request->all());

        ActivityLog::record('client.create', "Client créé : {$client->name}");

        return back()->with('success', " Client {$client->name} créé !");
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name'  => 'required|string|max:150',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'type'  => 'required|in:particulier,entreprise,revendeur',
        ]);

        $client->update($request->all());

        ActivityLog::record('client.update', "Client modifié : {$client->name}");

        return back()->with('success', " Client mis à jour !");
    }

    public function destroy(Client $client)
    {
        if ($client->sales()->count() > 0) {
            return back()->withErrors([
                'error' => '⚠️ Impossible de supprimer — ce client a des ventes associées.'
            ]);
        }

        $client->delete();
        ActivityLog::record('client.delete', "Client supprimé : {$client->name}");

        return back()->with('success', " Client supprimé !");
    }
}