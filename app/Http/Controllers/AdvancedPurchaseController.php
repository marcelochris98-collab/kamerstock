<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierReception;
use App\Models\SupplierReceptionItem;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SupplierHistory;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvancedPurchaseController extends Controller
{
    // === SUGGESTIONS DE REAPPROVISIONNEMENT ===
    public function suggestions()
    {
        // Produits dont la quantite est inferieure ou egale au seuil d'alerte
        $products = Product::with(['supplier', 'category'])
            ->where('is_active', true)
            ->whereColumn('quantity', '<=', 'alert_threshold')
            ->orderBy('supplier_id')
            ->get();

        // Grouper par fournisseur pour permettre de creer un bon de commande groupe
        $groupedProducts = $products->groupBy('supplier_id');
        $suppliers = Supplier::whereIn('id', $products->pluck('supplier_id')->unique())->get()->keyBy('id');

        return view('advanced_purchases.suggestions', compact('products', 'groupedProducts', 'suppliers'));
    }

    // === BONS DE COMMANDE (ORDERS) ===
    public function ordersIndex(Request $request)
    {
        $query = SupplierOrder::with(['supplier', 'user', 'items']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(20);

        return view('advanced_purchases.orders.index', compact('orders'));
    }

    public function ordersCreate(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Si on vient des suggestions, on peut pré-charger le fournisseur et les produits
        $preselectedSupplierId = $request->supplier_id;
        $preselectedProducts = [];
        if ($request->filled('products')) {
            $prodIds = is_array($request->products) ? $request->products : explode(',', $request->products);
            $preselectedProducts = Product::whereIn('id', $prodIds)->get();
        }

        return view('advanced_purchases.orders.create', compact('suppliers', 'products', 'preselectedSupplierId', 'preselectedProducts'));
    }

    public function ordersStore(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                $total = 0;
                $itemsData = [];

                foreach ($request->items as $item) {
                    $qty = (int) $item['quantity'];
                    $price = (float) $item['unit_price'];
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ];
                }

                $reference = 'CMD-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $order = SupplierOrder::create([
                    'supplier_id' => $request->supplier_id,
                    'user_id' => auth()->id(),
                    'reference' => $reference,
                    'total_amount' => $total,
                    'status' => 'commande',
                    'order_date' => $request->order_date,
                    'notes' => $request->notes,
                ]);

                foreach ($itemsData as $data) {
                    $data['supplier_order_id'] = $order->id;
                    $data['quantity_received'] = 0;
                    SupplierOrderItem::create($data);
                }

                ActivityLog::record('supplier_order.create', "Bon de commande fournisseur créé : {$order->reference}");

                return $order;
            });

            return redirect()
                ->route('advanced_purchases.orders.show', $order)
                ->with('success', 'Le bon de commande a été créé avec succès.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['metier' => $e->getMessage()]);
        }
    }

    public function ordersShow(SupplierOrder $order)
    {
        $order->load(['supplier', 'user', 'items.product', 'receptions.items.product']);
        return view('advanced_purchases.orders.show', compact('order'));
    }

    public function ordersReceive(Request $request, SupplierOrder $order)
    {
        $request->validate([
            'reception_date' => 'required|date',
            'notes' => 'nullable|string',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0',
        ]);

        if ($order->status === 'recu_complet' || $order->status === 'annule') {
            return back()->withErrors(['metier' => 'Ce bon de commande ne peut plus recevoir de livraisons.']);
        }

        try {
            DB::transaction(function () use ($request, $order) {
                $receptionRef = 'REC-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                
                $reception = SupplierReception::create([
                    'supplier_order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'reference' => $receptionRef,
                    'reception_date' => $request->reception_date,
                    'notes' => $request->notes,
                ]);

                $allReceived = true;

                foreach ($order->items as $item) {
                    $receivedThisTime = (int) ($request->quantities[$item->id] ?? 0);
                    if ($receivedThisTime > 0) {
                        SupplierReceptionItem::create([
                            'supplier_reception_id' => $reception->id,
                            'product_id' => $item->product_id,
                            'quantity' => $receivedThisTime,
                        ]);

                        // Incrémenter la quantité reçue sur la ligne de commande
                        $newQtyReceived = $item->quantity_received + $receivedThisTime;
                        $item->update(['quantity_received' => $newQtyReceived]);

                        // Mettre à jour le stock produit
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('quantity', $receivedThisTime);

                            // Tracer le mouvement de stock
                            StockMovement::create([
                                'product_id' => $product->id,
                                'user_id' => auth()->id(),
                                'type' => 'entree',
                                'quantity' => $receivedThisTime,
                                'reason' => "Réception commande {$order->reference} (Réf: {$reception->reference})",
                            ]);
                        }
                    }

                    // Vérifier si tout est reçu pour ce produit
                    if ($item->quantity_received < $item->quantity) {
                        $allReceived = false;
                    }
                }

                // Mettre à jour le statut du bon de commande
                $order->update([
                    'status' => $allReceived ? 'recu_complet' : 'recu_partiel'
                ]);

                ActivityLog::record('supplier_order.receive', "Réception enregistrée pour la commande {$order->reference} : {$reception->reference}");
            });

            return redirect()
                ->route('advanced_purchases.orders.show', $order)
                ->with('success', 'La réception de marchandise a été enregistrée et le stock a été mis à jour.');
        } catch (\Throwable $e) {
            return back()->withErrors(['metier' => 'Erreur lors de la réception : ' . $e->getMessage()]);
        }
    }

    public function ordersCancel(SupplierOrder $order)
    {
        if ($order->status !== 'commande' && $order->status !== 'brouillon') {
            return back()->withErrors(['metier' => 'Impossible d\'annuler une commande déjà reçue ou traitée.']);
        }

        $order->update(['status' => 'annule']);
        ActivityLog::record('supplier_order.cancel', "Bon de commande annulé : {$order->reference}");

        return redirect()->route('advanced_purchases.orders.index')->with('success', 'Bon de commande annulé avec succès.');
    }

    // Générer une facture d'achat à partir d'un bon de commande
    public function convertToInvoice(SupplierOrder $order)
    {
        if ($order->receptions->count() === 0) {
            return back()->withErrors(['metier' => 'Vous devez avoir enregistré au moins une réception pour générer la facture d\'achat.']);
        }

        // Vérifier s'il y a déjà un achat lié
        $existingPurchase = Purchase::where('notes', 'like', "%Facturation du bon de commande #{$order->reference}%")->first();
        if ($existingPurchase) {
            return redirect()
                ->route('purchases.show', $existingPurchase)
                ->with('warning', 'Une facture existe déjà pour ce bon de commande.');
        }

        try {
            $purchase = DB::transaction(function () use ($order) {
                // Créer l'achat (la facture)
                $total = 0;
                $itemsData = [];

                foreach ($order->items as $item) {
                    if ($item->quantity_received > 0) {
                        $subtotal = $item->quantity_received * $item->unit_price;
                        $total += $subtotal;
                        
                        $itemsData[] = [
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity_received,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $subtotal,
                        ];
                    }
                }

                $purchaseRef = 'ACH-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $purchase = Purchase::create([
                    'supplier_id' => $order->supplier_id,
                    'user_id' => auth()->id(),
                    'reference' => $purchaseRef,
                    'total_amount' => $total,
                    'amount_paid' => 0,
                    'amount_due' => $total,
                    'status' => 'en_attente',
                    'purchase_date' => now()->toDateString(),
                    'notes' => "Facturation du bon de commande #{$order->reference} (Reçu : {$order->status_label})",
                ]);

                foreach ($itemsData as $data) {
                    $data['purchase_id'] = $purchase->id;
                    PurchaseItem::create($data);
                }

                SupplierHistory::create([
                    'supplier_id' => $order->supplier_id,
                    'user_id' => auth()->id(),
                    'action' => 'purchase_created',
                    'title' => 'Facture d’achat créée',
                    'description' => "Facture {$purchase->reference} générée depuis la commande {$order->reference}.",
                    'amount' => $total,
                    'meta' => [
                        'purchase_id' => $purchase->id,
                        'order_id' => $order->id,
                    ],
                ]);

                ActivityLog::record('purchase.create', "Achat facturé depuis commande {$order->reference}");

                return $purchase;
            });

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'La facture d\'achat a été générée depuis le bon de commande.');
        } catch (\Throwable $e) {
            return back()->withErrors(['metier' => 'Erreur lors de la facturation : ' . $e->getMessage()]);
        }
    }

    // === RETOURS FOURNISSEURS (RETURNS) ===
    public function returnsIndex(Request $request)
    {
        $query = SupplierReturn::with(['supplier', 'purchase', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->latest()->paginate(20);

        return view('advanced_purchases.returns.index', compact('returns'));
    }

    public function returnsCreate(Request $request)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $purchases = Purchase::latest()->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        
        $selectedPurchase = null;
        if ($request->filled('purchase_id')) {
            $selectedPurchase = Purchase::with('items.product')->find($request->purchase_id);
        }

        return view('advanced_purchases.returns.create', compact('suppliers', 'purchases', 'products', 'selectedPurchase'));
    }

    public function returnsStore(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $return = DB::transaction(function () use ($request) {
                $total = 0;
                $itemsData = [];

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $qty = (int) $item['quantity'];
                    
                    if ($product->quantity < $qty) {
                        throw new \Exception("Stock insuffisant pour retourner « {$product->name} » (requis : {$qty}, disponible : {$product->quantity})");
                    }

                    $price = (float) $item['unit_price'];
                    $subtotal = $qty * $price;
                    $total += $subtotal;

                    $itemsData[] = [
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ];
                }

                $reference = 'RET-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $return = SupplierReturn::create([
                    'supplier_id' => $request->supplier_id,
                    'purchase_id' => $request->purchase_id,
                    'user_id' => auth()->id(),
                    'reference' => $reference,
                    'total_amount' => $total,
                    'status' => 'valide', // Validé immédiatement
                    'return_date' => $request->return_date,
                    'reason' => $request->reason,
                ]);

                foreach ($itemsData as $data) {
                    $data['supplier_return_id'] = $return->id;
                    SupplierReturnItem::create($data);

                    // Décrémenter le stock produit
                    $product = Product::find($data['product_id']);
                    $product->decrement('quantity', $data['quantity']);

                    // Tracer le mouvement de stock
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'sortie',
                        'quantity' => $data['quantity'],
                        'reason' => "Retour fournisseur {$return->reference}",
                    ]);
                }

                // Ajuster le montant dû de la facture d'achat si applicable
                if ($request->filled('purchase_id')) {
                    $purchase = Purchase::find($request->purchase_id);
                    if ($purchase && $purchase->amount_due > 0) {
                        $deduction = min($total, (float) $purchase->amount_due);
                        $newDue = max((float) $purchase->amount_due - $deduction, 0);
                        
                        $purchase->update([
                            'amount_due' => $newDue,
                            'status' => $newDue <= 0 ? 'solde' : $purchase->status,
                        ]);

                        SupplierHistory::create([
                            'supplier_id' => $return->supplier_id,
                            'user_id' => auth()->id(),
                            'action' => 'supplier_debt_reduced',
                            'title' => 'Dette réduite par retour',
                            'description' => "Dette réduite de " . number_format($deduction, 0, ',', ' ') . " FCFA sur l'achat {$purchase->reference} grâce au retour {$return->reference}.",
                            'amount' => $deduction,
                            'meta' => [
                                'purchase_id' => $purchase->id,
                                'return_id' => $return->id,
                            ]
                        ]);
                    }
                }

                ActivityLog::record('supplier_return.create', "Retour fournisseur validé : {$return->reference}");

                return $return;
            });

            return redirect()
                ->route('advanced_purchases.returns.show', $return)
                ->with('success', 'Le retour fournisseur a été enregistré, le stock décrémenté et la dette ajustée.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['metier' => $e->getMessage()]);
        }
    }

    public function returnsShow(SupplierReturn $return)
    {
        $return->load(['supplier', 'purchase', 'user', 'items.product']);
        return view('advanced_purchases.returns.show', compact('return'));
    }
}
