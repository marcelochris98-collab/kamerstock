<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Client;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\CreditSale;
use App\Models\CreditHistory;
use App\Services\ClientScoringService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['user', 'client', 'details'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $clients = Client::orderBy('name')->get();

        return view('sales.create', compact('products', 'clients'));
    }

    public function store(Request $request, ClientScoringService $scoringService, NotificationService $notificationService)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'payment_mode'       => 'required|in:cash,orange_money,mtn_money,credit,mixte',
            'amount_paid'        => 'nullable|numeric|min:0',
            'client_id'          => 'nullable|exists:clients,id',
            'client_name'        => 'required_without:client_id|string|max:255',
            'client_phone'       => 'required_without:client_id|string|max:50',
            'client_type'        => 'nullable|in:particulier,entreprise,revendeur',
            'client_email'       => 'nullable|email|max:255',
        ]);

        $details = [];
        $total = 0;
        $errors = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            if ($product->quantity < $item['quantity']) {
                $errors[] = "Stock insuffisant pour « {$product->name} » — disponible : {$product->quantity}.";
                continue;
            }

            $subtotal = $product->price_sell * $item['quantity'];
            $total += $subtotal;

            $details[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price_sell,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($details)) {
            $errors[] = 'Le panier est vide.';
        }

        if ($request->payment_mode === 'cash' && ((float) ($request->amount_paid ?? 0)) < $total) {
            $errors[] = "Montant insuffisant — il manque " . number_format($total - (float) $request->amount_paid, 0, ',', ' ') . " FCFA.";
        }

        if (!empty($errors)) {
            return back()->withInput()->withErrors(['metier' => $errors]);
        }

        $creditCreated = false;
        $stockAlerts = [];

        try {
            $sale = DB::transaction(function () use (
                $request,
                $details,
                $total,
                $scoringService,
                $notificationService,
                &$creditCreated,
                &$stockAlerts
            ) {
                if ($request->filled('client_id')) {
                    $client = Client::findOrFail($request->client_id);
                } else {
                    $cleanPhone = preg_replace('/\D+/', '', $request->client_phone);

                    $client = Client::whereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') = ?",
                        [$cleanPhone]
                    )->first();

                    if (!$client) {
                        $client = Client::create([
                            'name' => $request->client_name,
                            'phone' => $request->client_phone,
                            'email' => $request->client_email,
                            'type' => $request->client_type ?? 'particulier',
                        ]);
                    }
                }

                foreach ($details as $detail) {
                    $fresh = Product::lockForUpdate()->find($detail['product']->id);

                    if ($fresh->quantity < $detail['quantity']) {
                        throw new \Exception("Stock insuffisant pour « {$fresh->name} ».");
                    }
                }

                $isCredit = $request->payment_mode === 'credit';
                $amountPaid = (float) ($request->amount_paid ?? 0);

                if ($isCredit && $amountPaid > $total) {
                    $amountPaid = $total;
                }

                $changeDue = $isCredit ? 0 : max(0, $amountPaid - $total);
                $amountDue = max($total - $amountPaid, 0);

                $sale = Sale::create([
                    'user_id' => auth()->id(),
                    'client_id' => $client->id,
                    'total_amount' => $total,
                    'amount_paid' => $amountPaid,
                    'change_due' => $changeDue,
                    'payment_mode' => $request->payment_mode,
                    'status' => $amountDue > 0 ? 'credit' : 'completee',
                ]);

                foreach ($details as $detail) {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $detail['product']->id,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'subtotal' => $detail['subtotal'],
                    ]);

                    $detail['product']->decrement('quantity', $detail['quantity']);

                    $productAfterSale = Product::find($detail['product']->id);

                    if (
                        $productAfterSale &&
                        $productAfterSale->is_active &&
                        $productAfterSale->quantity <= $productAfterSale->alert_threshold
                    ) {
                        $stockAlerts[] = $productAfterSale->name;
                    }
                }

                if ($isCredit || $amountDue > 0) {
                    $credit = CreditSale::create([
                        'sale_id' => $sale->id,
                        'client_id' => $client->id,
                        'user_id' => auth()->id(),
                        'total_amount' => $total,
                        'amount_paid' => $amountPaid,
                        'amount_due' => $amountDue,
                        'status' => $amountPaid > 0 ? 'partiel' : 'en_attente',
                    ]);

                    $creditCreated = true;

                    CreditHistory::create([
                        'credit_sale_id' => $credit->id,
                        'user_id' => auth()->id(),
                        'action' => 'credit_created',
                        'title' => 'Crédit créé',
                        'description' => 'Crédit créé lors de la vente #' . $sale->id,
                        'amount' => $amountDue,
                        'meta' => [
                            'sale_id' => $sale->id,
                            'total_amount' => $total,
                            'amount_paid' => $amountPaid,
                            'amount_due' => $amountDue,
                        ],
                    ]);

                    $notificationService->notifyManagers(
                        'credit_created',
                        'Nouveau crédit client',
                        'Un crédit de ' . number_format($amountDue, 0, ',', ' ') . ' FCFA a été créé pour ' . $client->name . '.',
                        route('credits.show', $credit),
                        [
                            'credit_id' => $credit->id,
                            'client_id' => $client->id,
                            'amount_due' => $amountDue,
                        ]
                    );
                }

                $notificationService->notifyManagers(
                    'sale_created',
                    'Vente effectuée',
                    'Une vente de ' . number_format($total, 0, ',', ' ') . ' FCFA a été enregistrée pour ' . $client->name . '.',
                    route('sales.receipt', $sale),
                    [
                        'sale_id' => $sale->id,
                        'client_id' => $client->id,
                        'total' => $total,
                    ]
                );

                $scoringService->update($client);

                return $sale;
            });

            ActivityLog::record('sale.create', "Vente enregistrée — Total : {$total} FCFA");

            $toasts = [
                [
                    'type' => 'success',
                    'title' => 'Vente effectuée',
                    'message' => 'La vente a été enregistrée avec succès.',
                    'sound' => true,
                ],
            ];

            if ($creditCreated) {
                $toasts[] = [
                    'type' => 'warning',
                    'title' => 'Crédit créé',
                    'message' => 'Un crédit client a été généré pour cette vente.',
                    'sound' => true,
                ];
            }

            foreach (array_unique($stockAlerts) as $productName) {
                $toasts[] = [
                    'type' => 'danger',
                    'title' => 'Stock faible',
                    'message' => $productName . ' est maintenant en stock critique.',
                    'sound' => true,
                ];
            }

            return redirect()
                ->route('sales.receipt', $sale->id)
                ->with('success', 'Vente enregistrée !')
                ->with('toast_notifications', $toasts);
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'metier' => [$e->getMessage()],
                ]);
        }
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['user', 'client', 'details.product']);
        $settings = Setting::first();

        return view('sales.receipt', compact('sale', 'settings'));
    }

    public function destroy(Sale $sale)
    {
        if ($sale->status === 'annulee') {
            return back()->withErrors(['error' => 'Cette vente est déjà annulée.']);
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->details as $detail) {
                $detail->product->increment('quantity', $detail->quantity);
            }

            $sale->update(['status' => 'annulee']);
        });

        ActivityLog::record('sale.cancel', "Vente annulée : #{$sale->id}");

        return back()->with('success', 'Vente annulée — stock restauré !');
    }
}
