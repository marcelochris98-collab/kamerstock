<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('stock.index', compact('movements', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:entree,sortie',
            'quantity'   => 'required|integer|min:1|max:10000',
            'reason'     => 'nullable|string|max:255',
        ], [
            'product_id.required' => 'Sélectionnez un produit.',
            'quantity.min'        => 'La quantité doit être supérieure à 0.',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->type === 'sortie') {
            if ($product->quantity <= 0) {
                return back()->withErrors([
                    'quantity' => "Stock déjà à zéro pour « {$product->name} »."
                ]);
            }

            if ($product->quantity < $request->quantity) {
                return back()->withErrors([
                    'quantity' => "Stock insuffisant — disponible : {$product->quantity}, demandé : {$request->quantity}."
                ]);
            }
        }

        DB::transaction(function () use ($request, $product) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id'    => auth()->id(),
                'type'       => $request->type,
                'quantity'   => $request->quantity,
                'reason'     => $request->reason,
            ]);

            if ($request->type === 'entree') {
                $product->increment('quantity', $request->quantity);
            } else {
                $product->decrement('quantity', $request->quantity);
            }
        });

        $product->refresh();

        ActivityLog::record(
            'stock.movement',
            "Mouvement stock : {$request->type} de {$request->quantity} pour {$product->name}"
        );

        // Notifications persistantes
        if ($request->type === 'entree') {
            app(\App\Services\NotificationService::class)->notifyByPermission(
                'stock.view',
                'stock_entry',
                'Entrée de stock',
                "{$request->quantity} unités du produit {$product->name} ont été ajoutées.",
                route('products.show', $product->id),
                ['product_id' => $product->id, 'quantity' => $request->quantity],
                'stock'
            );
        } else {
            app(\App\Services\NotificationService::class)->notifyByPermission(
                'stock.view',
                'stock_exit',
                'Sortie de stock',
                "{$request->quantity} unités du produit {$product->name} ont été retirées.",
                route('products.show', $product->id),
                ['product_id' => $product->id, 'quantity' => $request->quantity],
                'stock'
            );
        }

        if ($product->is_active && $product->quantity <= $product->alert_threshold) {
            $isRupture = $product->quantity <= 0;
            app(\App\Services\NotificationService::class)->notifyByPermission(
                'stock.view',
                $isRupture ? 'stock_empty' : 'stock_low',
                $isRupture ? 'Rupture de stock' : 'Stock faible',
                $isRupture ? "Le produit {$product->name} est en rupture de stock !" : "Le produit {$product->name} a atteint le seuil d'alerte (Reste : {$product->quantity} unités).",
                route('products.show', $product->id),
                ['product_id' => $product->id, 'quantity' => $product->quantity],
                'stock'
            );
        }

        $toasts = [
            [
                'type' => 'success',
                'title' => 'Mouvement enregistré',
                'message' => 'Le mouvement de stock a été enregistré avec succès.',
                'sound' => 'envoi',
            ],
        ];

        if (
            $product->is_active &&
            $product->quantity <= $product->alert_threshold
        ) {
            $toasts[] = [
                'type' => 'danger',
                'title' => 'Stock faible',
                'message' => $product->name . ' est maintenant en stock critique.',
                'sound' => 'alerte',
            ];
        }

        return back()
            ->with('success', 'Mouvement de stock enregistré !')
            ->with('toast_notifications', $toasts);
    }
}
