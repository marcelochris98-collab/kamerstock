<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteDetail;
use App\Models\Product;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['client', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $quotes = $query->orderBy('created_at', 'desc')->paginate(20);
        $clients = Client::orderBy('name')->get();

        return view('quotes.index', compact('quotes', 'clients'));
    }

    public function create()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $clients = Client::orderBy('name')->get();
        $settings = Setting::first();

        return view('quotes.create', compact('products', 'clients', 'settings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'type'               => 'required|in:devis,proforma',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'valid_until'        => 'nullable|date|after_or_equal:today',
            'notes'              => 'nullable|string',
        ]);

        $client = Client::findOrFail($request->client_id);
        $settings = Setting::first();
        $taxRate = $settings ? (float)$settings->tax_rate : 17.5;

        $subtotal = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $price = $product->getPriceForType($client->type);
            $qty = intval($item['quantity']);
            $itemSubtotal = $price * $qty;
            
            $subtotal += $itemSubtotal;
            $itemsData[] = [
                'product_id' => $product->id,
                'quantity'   => $qty,
                'unit_price' => $price,
                'subtotal'   => $itemSubtotal,
            ];
        }

        if (empty($itemsData)) {
            return back()->withInput()->withErrors(['metier' => 'Le devis ne contient aucun article valide.']);
        }

        $taxAmount = ($subtotal * $taxRate) / 100;
        $totalAmount = $subtotal + $taxAmount;

        $year = date('Y');
        $rand = strtoupper(substr(uniqid(), -5));
        $prefix = $request->type === 'devis' ? 'DEV' : 'PROF';
        $reference = "{$prefix}-{$year}-{$rand}";

        try {
            $quote = DB::transaction(function () use ($request, $client, $reference, $subtotal, $taxAmount, $totalAmount, $itemsData) {
                $quote = Quote::create([
                    'client_id'          => $client->id,
                    'user_id'            => auth()->id(),
                    'reference'          => $reference,
                    'type'               => $request->type,
                    'subtotal'           => $subtotal,
                    'tax_amount'         => $taxAmount,
                    'discount_amount'    => 0,
                    'total_amount'       => $totalAmount,
                    'status'             => 'brouillon',
                    'valid_until'        => $request->valid_until,
                    'notes'              => $request->notes,
                ]);

                foreach ($itemsData as $item) {
                    QuoteDetail::create([
                        'quote_id'   => $quote->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount'   => 0,
                        'subtotal'   => $item['subtotal'],
                    ]);
                }

                return $quote;
            });

            ActivityLog::record('quote.create', "Devis/Proforma créé : {$quote->reference}");

            return redirect()
                ->route('quotes.show', $quote->id)
                ->with('success', 'Le document a été créé avec succès.');

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors(['metier' => 'Erreur lors de la création : ' . $e->getMessage()]);
        }
    }

    public function show(Quote $quote)
    {
        $quote->load(['client', 'user', 'details.product']);
        $settings = Setting::first();

        return view('quotes.show', compact('quote', 'settings'));
    }

    public function print(Quote $quote)
    {
        $quote->load(['client', 'user', 'details.product']);
        $settings = Setting::first();

        return view('quotes.print', compact('quote', 'settings'));
    }

    public function convertToSale(Quote $quote)
    {
        if ($quote->status === 'converti') {
            return back()->withErrors(['error' => 'Ce document a déjà été converti en vente.']);
        }

        $quote->load('details.product');
        $errors = [];

        // Check stocks
        foreach ($quote->details as $detail) {
            if ($detail->product->quantity < $detail->quantity) {
                $errors[] = "Stock insuffisant pour « {$detail->product->name} » (requis : {$detail->quantity}, disponible : {$detail->product->quantity}).";
            }
        }

        if (!empty($errors)) {
            return back()->withErrors(['metier' => $errors]);
        }

        try {
            $sale = DB::transaction(function () use ($quote) {
                // Create Sale
                $sale = Sale::create([
                    'user_id'      => auth()->id(),
                    'client_id'    => $quote->client_id,
                    'total_amount' => $quote->total_amount,
                    'amount_paid'  => $quote->total_amount, // default paid in full
                    'change_due'   => 0,
                    'discount'     => $quote->discount_amount,
                    'payment_mode' => 'cash',
                    'status'       => 'completee',
                    'notes'        => "Converti depuis le " . $quote->type_label . " #" . $quote->reference,
                ]);

                foreach ($quote->details as $detail) {
                    SaleDetail::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $detail->product_id,
                        'quantity'   => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'subtotal'   => $detail->subtotal,
                    ]);

                    // Decrement stock
                    $detail->product->decrement('quantity', $detail->quantity);
                }

                // Update Quote status
                $quote->update([
                    'status'             => 'converti',
                    'converted_sale_id'  => $sale->id,
                ]);

                return $sale;
            });

            ActivityLog::record('quote.convert', "Document {$quote->reference} converti en vente #{$sale->id}");

            return redirect()
                ->route('sales.receipt', $sale->id)
                ->with('success', 'Devis converti en vente avec succès !');

        } catch (\Throwable $e) {
            return back()->withErrors(['metier' => 'Erreur lors de la conversion : ' . $e->getMessage()]);
        }
    }

    public function destroy(Quote $quote)
    {
        if ($quote->status === 'converti') {
            return back()->withErrors(['error' => 'Impossible de supprimer un devis déjà converti.']);
        }

        $quote->delete();
        ActivityLog::record('quote.delete', "Document supprimé : {$quote->reference}");

        return redirect()
            ->route('quotes.index')
            ->with('success', 'Le document a été supprimé.');
    }
}
