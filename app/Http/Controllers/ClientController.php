<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\ClientScoringService;


class ClientController extends Controller
{
   public function index(ClientScoringService $scoringService)
{
    $clients = Client::withCount('sales')
        ->orderBy('name')
        ->paginate(20);

    foreach ($clients as $client) {
        $scoringService->update($client);
    }

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
    public function show(Client $client, ClientScoringService $scoringService)
{
    $client = $scoringService->update($client);

    $client->load([
        'sales' => fn ($query) => $query->latest()->limit(10),
        'creditSales' => fn ($query) => $query->latest()->limit(10),
    ]);

    return view('clients.show', compact('client'));
}
public function lookup(Request $request)
{
    $phone = preg_replace('/\D+/', '', $request->phone ?? '');
    $name = trim($request->name ?? '');

    $query = Client::query();

    if ($phone !== '') {
        $query->whereRaw(
            "REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') LIKE ?",
            ["%{$phone}%"]
        );
    }

    if ($phone === '' && $name !== '') {
        $query->where('name', 'like', "%{$name}%");
    }

    $clients = $query->limit(5)->get();

    return response()->json([
        'found' => $clients->isNotEmpty(),
        'clients' => $clients->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'type' => $client->type_label,
                'score' => $client->loyalty_score ?? 0,
                'status' => $client->loyalty_status ?? 'occasionnel',
                'credit_available' => $client->credit_available ?? 0,
            ];
        }),
    ]);
}
}