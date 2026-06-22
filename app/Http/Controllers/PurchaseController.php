<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierHistory;
use App\Models\SupplierPayment;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'user', 'items'])
            ->latest()
            ->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'nullable|string|max:150',
            'supplier_phone' => 'nullable|string|max:50',
            'supplier_email' => 'nullable|email|max:150',

            'purchase_date' => 'nullable|date',
            'payment_method' => 'required|in:cash,orange_money,mtn_money,virement',
            'amount_paid' => 'nullable|numeric|min:0',
            'previous_debt_payment' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',

            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:existing,new',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'nullable|string|max:150',
            'items.*.reference' => 'nullable|string|max:50',
            'items.*.category_id' => 'nullable|exists:categories,id',
            'items.*.category_name' => 'nullable|string|max:150',
            'items.*.unit' => 'nullable|in:piece,metre,kg,litre,boite,sachet,carton,paquet,flacon,tube,kit,lot,palette,sac',
            'items.*.alert_threshold' => 'nullable|integer|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.sale_price' => 'nullable|numeric|min:0',
            'items.*.update_prices' => 'nullable|boolean',
        ]);

        if (!$request->filled('supplier_id') && !$request->filled('supplier_name')) {
            return back()->withInput()->withErrors([
                'supplier_name' => 'Sélectionnez un fournisseur existant ou renseignez un nouveau fournisseur.'
            ]);
        }

        try {
            $purchase = DB::transaction(function () use ($request) {
                $supplier = $this->resolveSupplier($request);

                $details = [];
                $total = 0;

                foreach ($request->items as $item) {
                    $product = $this->resolveProduct($item, $supplier);

                    $quantity = (int) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $salePrice = isset($item['sale_price']) ? (float) $item['sale_price'] : null;
                    $subtotal = $quantity * $unitPrice;

                    if (!empty($item['update_prices'])) {
                        $updateData = ['price_buy' => $unitPrice];

                        if ($salePrice !== null && $salePrice > 0) {
                            $updateData['price_sell'] = $salePrice;
                        }

                        $product->update($updateData);
                    }

                    $total += $subtotal;

                    $details[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];
                }

                $amountPaid = (float) ($request->amount_paid ?? 0);

                if ($amountPaid > $total) {
                    $amountPaid = $total;
                }

                $amountDue = max($total - $amountPaid, 0);

                $status = match (true) {
                    $amountDue <= 0 => 'solde',
                    $amountPaid > 0 => 'partiel',
                    default => 'en_attente',
                };

                $purchase = Purchase::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => auth()->id(),
                    'reference' => $this->generatePurchaseReference(),
                    'total_amount' => $total,
                    'amount_paid' => $amountPaid,
                    'amount_due' => $amountDue,
                    'status' => $status,
                    'purchase_date' => $request->purchase_date ?? now()->toDateString(),
                    'notes' => $request->notes,
                ]);

                foreach ($details as $detail) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $detail['product']->id,
                        'quantity' => $detail['quantity'],
                        'unit_price' => $detail['unit_price'],
                        'subtotal' => $detail['subtotal'],
                    ]);

                    $detail['product']->increment('quantity', $detail['quantity']);

                    StockMovement::create([
                        'product_id' => $detail['product']->id,
                        'user_id' => auth()->id(),
                        'type' => 'entree',
                        'quantity' => $detail['quantity'],
                        'reason' => 'Achat fournisseur ' . $purchase->reference,
                    ]);
                }

                if ($amountPaid > 0) {
                    SupplierPayment::create([
                        'supplier_id' => $supplier->id,
                        'purchase_id' => $purchase->id,
                        'user_id' => auth()->id(),
                        'amount' => $amountPaid,
                        'payment_method' => $request->payment_method,
                        'internal_reference' => $this->generatePaymentReference($request->payment_method),
                        'notes' => 'Paiement de l’achat courant ' . $purchase->reference,
                    ]);
                }

                $previousDebtPayment = (float) ($request->previous_debt_payment ?? 0);

                if ($previousDebtPayment > 0) {
                    $this->applyPreviousDebtPayment($supplier, $previousDebtPayment, $purchase, $request->payment_method);
                }

                SupplierHistory::create([
                    'supplier_id' => $supplier->id,
                    'user_id' => auth()->id(),
                    'action' => 'purchase_created',
                    'title' => 'Achat fournisseur créé',
                    'description' => 'Achat ' . $purchase->reference . ' enregistré pour ' . number_format($total, 0, ',', ' ') . ' FCFA.',
                    'amount' => $total,
                    'meta' => [
                        'purchase_id' => $purchase->id,
                        'reference' => $purchase->reference,
                        'amount_paid' => $amountPaid,
                        'amount_due' => $amountDue,
                    ],
                ]);

                if ($amountDue > 0) {
                    SupplierHistory::create([
                        'supplier_id' => $supplier->id,
                        'user_id' => auth()->id(),
                        'action' => 'supplier_debt_created',
                        'title' => 'Dette fournisseur créée',
                        'description' => 'Dette de ' . number_format($amountDue, 0, ',', ' ') . ' FCFA créée sur l’achat ' . $purchase->reference . '.',
                        'amount' => $amountDue,
                        'meta' => [
                            'purchase_id' => $purchase->id,
                            'reference' => $purchase->reference,
                            'amount_due' => $amountDue,
                        ],
                    ]);
                }

                ActivityLog::record('purchase.create', "Achat fournisseur créé : {$purchase->reference}");

                return $purchase;
            });

            return redirect()
                ->route('purchases.show', $purchase)
                ->with('success', 'Achat fournisseur enregistré avec succès.')
                ->with('toast_notifications', [
                    [
                        'type' => 'success',
                        'title' => 'Achat fournisseur',
                        'message' => 'L’achat a été enregistré, le stock mis à jour et la dette calculée.',
                        'sound' => true,
                    ],
                ]);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'metier' => [$e->getMessage()],
            ]);
        }
    }

    private function resolveSupplier(Request $request): Supplier
    {
        if ($request->filled('supplier_id')) {
            return Supplier::findOrFail($request->supplier_id);
        }

        $cleanPhone = preg_replace('/\D+/', '', $request->supplier_phone ?? '');
        $supplier = null;

        if ($cleanPhone !== '') {
            $supplier = Supplier::whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '.', '') = ?",
                [$cleanPhone]
            )->first();
        }

        if (!$supplier) {
            $supplier = Supplier::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($request->supplier_name))])->first();
        }

        if (!$supplier) {
            $supplier = Supplier::create([
                'name' => trim($request->supplier_name),
                'phone' => $request->supplier_phone,
                'email' => $request->supplier_email,
                'is_active' => true,
            ]);

            ActivityLog::record('supplier.create', "Fournisseur créé automatiquement : {$supplier->name}");
        }

        return $supplier;
    }

    private function resolveProduct(array $item, Supplier $supplier): Product
    {
        if ($item['type'] === 'existing') {
            if (empty($item['product_id'])) {
                throw new \Exception('Veuillez sélectionner un produit existant.');
            }

            return Product::findOrFail($item['product_id']);
        }

        if (empty($item['product_name']) || empty($item['reference'])) {
            throw new \Exception('Pour un nouveau produit, le nom et la référence sont obligatoires.');
        }

        if (Product::where('reference', $item['reference'])->exists()) {
            throw new \Exception('La référence produit « ' . $item['reference'] . ' » existe déjà.');
        }

        $categoryId = $item['category_id'] ?? null;

        if (!$categoryId && !empty($item['category_name'])) {
            $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($item['category_name']))])->first();

            if (!$category) {
                $category = Category::create([
                    'name' => trim($item['category_name']),
                ]);

                ActivityLog::record('category.create', "Catégorie créée automatiquement : {$category->name}");
            }

            $categoryId = $category->id;
        }

        $unitPrice = (float) $item['unit_price'];
        $salePrice = isset($item['sale_price']) && (float) $item['sale_price'] > 0
            ? (float) $item['sale_price']
            : $unitPrice;

        $product = Product::create([
            'name' => trim($item['product_name']),
            'reference' => trim($item['reference']),
            'category_id' => $categoryId,
            'supplier_id' => $supplier->id,
            'unit' => $item['unit'] ?? 'piece',
            'price_buy' => $unitPrice,
            'price_sell' => $salePrice,
            'quantity' => 0,
            'alert_threshold' => $item['alert_threshold'] ?? 5,
            'is_active' => true,
        ]);

        ActivityLog::record('product.create', "Produit créé automatiquement depuis achat : {$product->name}");

        return $product;
    }

    private function applyPreviousDebtPayment(Supplier $supplier, float $amount, Purchase $currentPurchase, string $paymentMethod): void
    {
        $remaining = $amount;

        $oldDebts = Purchase::where('supplier_id', $supplier->id)
            ->where('id', '!=', $currentPurchase->id)
            ->where('amount_due', '>', 0)
            ->whereIn('status', ['en_attente', 'partiel'])
            ->oldest()
            ->get();

        foreach ($oldDebts as $debtPurchase) {
            if ($remaining <= 0) {
                break;
            }

            $paymentAmount = min($remaining, (float) $debtPurchase->amount_due);

            SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'purchase_id' => $debtPurchase->id,
                'user_id' => auth()->id(),
                'amount' => $paymentAmount,
                'payment_method' => $paymentMethod,
                'internal_reference' => $this->generatePaymentReference($paymentMethod),
                'notes' => 'Paiement d’ancienne dette effectué lors de l’achat ' . $currentPurchase->reference,
            ]);

            $newPaid = (float) $debtPurchase->amount_paid + $paymentAmount;
            $newDue = max((float) $debtPurchase->total_amount - $newPaid, 0);

            $debtPurchase->update([
                'amount_paid' => $newPaid,
                'amount_due' => $newDue,
                'status' => $newDue <= 0 ? 'solde' : 'partiel',
            ]);

            SupplierHistory::create([
                'supplier_id' => $supplier->id,
                'user_id' => auth()->id(),
                'action' => $newDue <= 0 ? 'supplier_debt_closed' : 'supplier_debt_partial_payment',
                'title' => $newDue <= 0 ? 'Ancienne dette soldée' : 'Ancienne dette réduite',
                'description' => 'Paiement de ' . number_format($paymentAmount, 0, ',', ' ') . ' FCFA sur l’achat ' . $debtPurchase->reference . '.',
                'amount' => $paymentAmount,
                'meta' => [
                    'purchase_id' => $debtPurchase->id,
                    'current_purchase_id' => $currentPurchase->id,
                    'remaining_due' => $newDue,
                ],
            ]);

            $remaining -= $paymentAmount;
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product', 'payments.user']);

        return view('purchases.show', compact('purchase'));
    }

    public function debts()
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->whereIn('status', ['en_attente', 'partiel'])
            ->where('amount_due', '>', 0)
            ->latest()
            ->paginate(20);

        return view('purchases.debts', compact('purchases'));
    }

    public function payment(Request $request, Purchase $purchase)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $purchase->amount_due,
            'payment_method' => 'required|in:cash,orange_money,mtn_money,virement',
            'external_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $purchase) {
            $amount = (float) $request->amount;
            $internalReference = $this->generatePaymentReference($request->payment_method);

            SupplierPayment::create([
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'internal_reference' => $internalReference,
                'external_reference' => $request->external_reference,
                'reference' => $request->external_reference,
                'notes' => $request->notes,
            ]);

            $newPaid = (float) $purchase->amount_paid + $amount;
            $newDue = max((float) $purchase->total_amount - $newPaid, 0);

            $purchase->update([
                'amount_paid' => $newPaid,
                'amount_due' => $newDue,
                'status' => $newDue <= 0 ? 'solde' : 'partiel',
            ]);

            SupplierHistory::create([
                'supplier_id' => $purchase->supplier_id,
                'user_id' => auth()->id(),
                'action' => $newDue <= 0 ? 'supplier_debt_closed' : 'supplier_payment_added',
                'title' => $newDue <= 0 ? 'Dette fournisseur soldée' : 'Paiement fournisseur enregistré',
                'description' => 'Paiement de ' . number_format($amount, 0, ',', ' ') . ' FCFA sur l’achat ' . $purchase->reference . '.',
                'amount' => $amount,
                'meta' => [
                    'purchase_id' => $purchase->id,
                    'reference' => $purchase->reference,
                    'remaining_due' => $newDue,
                ],
            ]);
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Paiement fournisseur enregistré avec succès.')
            ->with('toast_notifications', [
                [
                    'type' => 'success',
                    'title' => 'Paiement fournisseur',
                    'message' => 'Le paiement fournisseur a été enregistré.',
                    'sound' => true,
                ],
            ]);
    }

    public function paymentsHistory()
    {
        $payments = SupplierPayment::with(['supplier', 'purchase', 'user'])
            ->latest()
            ->paginate(20);

        return view('purchases.payments-history', compact('payments'));
    }

    public function dashboard()
    {
        $totalPurchases = Purchase::sum('total_amount');
        $totalPaid = Purchase::sum('amount_paid');
        $totalDue = Purchase::sum('amount_due');

        $recentPurchases = Purchase::with('supplier')->latest()->limit(5)->get();

        $topSuppliers = DB::table('suppliers')
            ->leftJoin('purchases', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.id',
                'suppliers.name',
                'suppliers.phone',
                DB::raw('COALESCE(SUM(purchases.total_amount), 0) as purchases_sum_total_amount')
            )
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone')
            ->orderByDesc('purchases_sum_total_amount')
            ->limit(5)
            ->get();

        return view('purchases.dashboard', compact(
            'totalPurchases',
            'totalPaid',
            'totalDue',
            'recentPurchases',
            'topSuppliers'
        ));
    }

    private function generatePurchaseReference(): string
    {
        $date = now()->format('Ymd');
        $count = Purchase::whereDate('created_at', now()->toDateString())->count() + 1;

        return 'ACH-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function generatePaymentReference(string $method): string
    {
        $prefix = match ($method) {
            'orange_money' => 'OMF',
            'mtn_money' => 'MOMOF',
            'virement' => 'VIRF',
            default => 'CASHF',
        };

        $date = now()->format('Ymd');

        $count = SupplierPayment::where('payment_method', $method)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $prefix . '-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}