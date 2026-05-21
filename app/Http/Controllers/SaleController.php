<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Client;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

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

    public function store(Request $request)
    {
        $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'payment_mode'       => 'required|in:cash,orange_money,mtn_money,cheque,credit,mixte',
            'amount_paid'        => 'nullable|numeric|min:0',
            'client_id'          => 'nullable|exists:clients,id',
        ]);

        $details = [];
        $total   = 0;
        $errors  = [];

        // Vente à crédit sans client
        if ($request->payment_mode === 'credit' && !$request->client_id) {
            $errors[] = ' Une vente à crédit nécessite un client identifié.';
        }

        foreach ($request->items as $index => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            if ($product->quantity < $item['quantity']) {
                $errors[] = " Stock insuffisant pour « {$product->name} » — disponible : {$product->quantity}.";
                continue;
            }

            $subtotal  = $product->price_sell * $item['quantity'];
            $total    += $subtotal;
            $details[] = [
                'product'    => $product,
                'quantity'   => $item['quantity'],
                'unit_price' => $product->price_sell,
                'subtotal'   => $subtotal,
            ];
        }

        if ($request->payment_mode === 'cash' && ($request->amount_paid ?? 0) < $total) {
            $errors[] = " Montant insuffisant — il manque " . number_format($total - $request->amount_paid, 0, ',', ' ') . " FCFA.";
        }

        if (empty($details)) {
            $errors[] = ' Le panier est vide.';
        }

        if (!empty($errors)) {
            return back()->withInput()->withErrors(['metier' => $errors]);
        }

        DB::transaction(function () use ($request, $details, $total) {
            foreach ($details as $detail) {
                $fresh = Product::lockForUpdate()->find($detail['product']->id);
                if ($fresh->quantity < $detail['quantity']) {
                    throw new \Exception("Stock insuffisant pour « {$fresh->name} ».");
                }
            }

            $isCredit   = $request->payment_mode === 'credit';
            $amountPaid = $isCredit ? 0 : ($request->amount_paid ?? 0);
            $changeDue  = $isCredit ? 0 : max(0, $amountPaid - $total);

            $sale = Sale::create([
                'user_id'      => auth()->id(),
                'client_id'    => $request->client_id,
                'total_amount' => $total,
                'amount_paid'  => $amountPaid,
                'change_due'   => $changeDue,
                'payment_mode' => $request->payment_mode,
                'status'       => $isCredit ? 'credit' : 'completee',
            ]);

            foreach ($details as $detail) {
                SaleDetail::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $detail['product']->id,
                    'quantity'   => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal'   => $detail['subtotal'],
                ]);
                $detail['product']->decrement('quantity', $detail['quantity']);
            }

            session(['last_sale_id' => $sale->id]);
        });

        ActivityLog::record('sale.create', "Vente enregistrée — Total : {$total} FCFA");

        return redirect()->route('sales.receipt', session('last_sale_id'))
                         ->with('success', ' Vente enregistrée !');
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['user', 'client', 'details.product']);
        $settings = Setting::first();

        // CORRECTION : Changement de 'ventes.receipt' vers 'sales.receipt'
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

        return back()->with('success', ' Vente annulée — stock restauré !');
    }
}
