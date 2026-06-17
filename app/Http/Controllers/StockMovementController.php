<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $totalStockValue = Product::where('is_active', true)
            ->selectRaw('COALESCE(SUM(quantity * price_buy), 0) as value')
            ->value('value');

        $activeProductsCount = Product::where('is_active', true)->count();
        $criticalProductsCount = Product::where('is_active', true)
            ->whereColumn('quantity', '<=', 'alert_threshold')
            ->where('quantity', '>', 0)
            ->count();
        $outOfStockCount = Product::where('is_active', true)
            ->where('quantity', '<=', 0)
            ->count();

        $criticalProducts = Product::with('category')
            ->where('is_active', true)
            ->whereColumn('quantity', '<=', 'alert_threshold')
            ->orderBy('quantity')
            ->limit(8)
            ->get();

        $incomingToday = StockMovement::where('type', 'entree')
            ->whereDate('created_at', today())
            ->sum('quantity');

        $outgoingToday = StockMovement::where('type', 'sortie')
            ->whereDate('created_at', today())
            ->sum('quantity');

        $fastMovingProducts = Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(SUM(CASE WHEN sales.id IS NOT NULL AND sales.status != ? AND sales.created_at >= ? THEN sale_details.quantity ELSE 0 END), 0) as sold_quantity', [
                'annulee',
                now()->subDays(30),
            ])
            ->leftJoin('sale_details', 'sale_details.product_id', '=', 'products.id')
            ->leftJoin('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->where('products.is_active', true)
            ->groupBy(
                'products.id',
                'products.category_id',
                'products.supplier_id',
                'products.name',
                'products.reference',
                'products.unit',
                'products.price_buy',
                'products.price_sell',
                'products.price_sell_company',
                'products.price_sell_reseller',
                'products.price_sell_wholesale',
                'products.quantity',
                'products.alert_threshold',
                'products.tax_rate',
                'products.description',
                'products.is_active',
                'products.created_at',
                'products.updated_at'
            )
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get();

        $dormantProducts = Product::where('is_active', true)
            ->whereDoesntHave('saleDetails.sale', function ($query) {
                $query->where('status', '!=', 'annulee')
                    ->where('created_at', '>=', now()->subDays(90));
            })
            ->orderByDesc('quantity')
            ->limit(5)
            ->get();

        return view('stock.index', compact(
            'products',
            'totalStockValue',
            'activeProductsCount',
            'criticalProductsCount',
            'outOfStockCount',
            'criticalProducts',
            'incomingToday',
            'outgoingToday',
            'fastMovingProducts',
            'dormantProducts'
        ));
    }

    public function history(Request $request)
    {
        $movements = StockMovement::with(['product', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('reason', 'like', "%{$search}%")
                        ->orWhere('reference_type', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('reference', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->type))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock.history', compact('movements', 'products'));
    }

    public function store(Request $request)
    {
        if ($request->input('action') === 'inventory') {
            return $this->storeInventoryAdjustment($request);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:entree,sortie',
            'quantity' => 'required|integer|min:1|max:10000',
            'movement_category' => 'required|in:reception,retour_client,retour_fournisseur,perte_casse,ajustement,autre',
            'reason' => 'nullable|string|max:255',
        ], [
            'product_id.required' => 'Selectionnez un produit.',
            'quantity.min' => 'La quantite doit etre superieure a 0.',
        ]);

        $product = Product::lockForUpdate()->findOrFail($request->product_id);
        $quantity = (int) $request->quantity;

        if ($request->type === 'sortie' && $product->quantity < $quantity) {
            return back()->withErrors([
                'quantity' => "Stock insuffisant - disponible : {$product->quantity}, demande : {$quantity}."
            ])->withInput();
        }

        DB::transaction(function () use ($request, $product, $quantity) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'quantity' => $quantity,
                'reason' => $this->movementReason($request->movement_category, $request->reason),
                'reference_type' => $request->movement_category,
            ]);

            if ($request->type === 'entree') {
                $product->increment('quantity', $quantity);
            } else {
                $product->decrement('quantity', $quantity);
            }
        });

        $product->refresh();

        ActivityLog::record(
            'stock.movement',
            "Mouvement stock : {$request->type} de {$quantity} pour {$product->name}"
        );

        $this->notifyStockMovement($request->type, $product, $quantity);
        $this->notifyLowStockIfNeeded($product);

        $toasts = [[
            'type' => 'success',
            'title' => 'Mouvement enregistre',
            'message' => 'Le mouvement de stock a ete enregistre avec succes.',
            'sound' => 'envoi',
        ]];

        if ($product->is_active && $product->quantity <= $product->alert_threshold) {
            $toasts[] = [
                'type' => 'danger',
                'title' => 'Stock faible',
                'message' => $product->name . ' est maintenant en stock critique.',
                'sound' => 'alerte',
            ];
        }

        return back()
            ->with('success', 'Mouvement de stock enregistre !')
            ->with('toast_notifications', $toasts);
    }

    private function storeInventoryAdjustment(Request $request)
    {
        $request->validate([
            'inventory_product_id' => 'required|exists:products,id',
            'counted_quantity' => 'required|integer|min:0|max:100000',
            'inventory_reason' => 'nullable|string|max:255',
        ], [
            'inventory_product_id.required' => 'Selectionnez le produit compte.',
            'counted_quantity.required' => 'Renseignez la quantite comptee.',
        ]);

        $product = Product::lockForUpdate()->findOrFail($request->inventory_product_id);
        $countedQuantity = (int) $request->counted_quantity;
        $currentQuantity = (int) $product->quantity;
        $difference = $countedQuantity - $currentQuantity;

        if ($difference === 0) {
            return back()->with('success', 'Inventaire valide : aucun ecart constate.');
        }

        $type = $difference > 0 ? 'entree' : 'sortie';
        $quantity = abs($difference);

        DB::transaction(function () use ($request, $product, $countedQuantity, $currentQuantity, $type, $quantity) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $quantity,
                'reason' => trim("Inventaire physique : stock systeme {$currentQuantity}, comptage {$countedQuantity}. " . ($request->inventory_reason ?? '')),
                'reference_type' => 'inventaire',
            ]);

            $product->update(['quantity' => $countedQuantity]);
        });

        ActivityLog::record(
            'stock.inventory',
            "Inventaire {$product->name} : ecart {$difference}, stock valide a {$countedQuantity}"
        );

        $product->refresh();
        $this->notifyLowStockIfNeeded($product);

        return back()
            ->with('success', 'Inventaire valide et ecart enregistre.')
            ->with('toast_notifications', [[
                'type' => 'success',
                'title' => 'Inventaire valide',
                'message' => "Le stock de {$product->name} est maintenant a {$countedQuantity}.",
                'sound' => 'envoi',
            ]]);
    }

    private function movementReason(string $category, ?string $reason): string
    {
        $label = match ($category) {
            'reception' => 'Reception commande',
            'retour_client' => 'Retour client',
            'retour_fournisseur' => 'Retour fournisseur',
            'perte_casse' => 'Perte / casse',
            'ajustement' => 'Ajustement manuel',
            default => 'Mouvement manuel',
        };

        return trim($label . ($reason ? " - {$reason}" : ''));
    }

    private function notifyStockMovement(string $type, Product $product, int $quantity): void
    {
        if ($type === 'entree') {
            app(\App\Services\NotificationService::class)->notifyByPermission(
                'stock.view',
                'stock_entry',
                'Entree de stock',
                "{$quantity} unites du produit {$product->name} ont ete ajoutees.",
                route('products.show', $product->id),
                ['product_id' => $product->id, 'quantity' => $quantity],
                'stock'
            );

            return;
        }

        app(\App\Services\NotificationService::class)->notifyByPermission(
            'stock.view',
            'stock_exit',
            'Sortie de stock',
            "{$quantity} unites du produit {$product->name} ont ete retirees.",
            route('products.show', $product->id),
            ['product_id' => $product->id, 'quantity' => $quantity],
            'stock'
        );
    }

    private function notifyLowStockIfNeeded(Product $product): void
    {
        if (! $product->is_active || $product->quantity > $product->alert_threshold) {
            return;
        }

        $isOutOfStock = $product->quantity <= 0;

        app(\App\Services\NotificationService::class)->notifyByPermission(
            'stock.view',
            $isOutOfStock ? 'stock_empty' : 'stock_low',
            $isOutOfStock ? 'Rupture de stock' : 'Stock faible',
            $isOutOfStock
                ? "Le produit {$product->name} est en rupture de stock !"
                : "Le produit {$product->name} a atteint le seuil d'alerte (reste : {$product->quantity} unites).",
            route('products.show', $product->id),
            ['product_id' => $product->id, 'quantity' => $product->quantity],
            'stock'
        );
    }
}
