<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\CreditSale;
use App\Models\SupplierOrder;
use App\Models\Category;
use App\Models\Quote;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->q);
        
        if (strlen($q) < 2) {
            return response()->json([
                'results' => []
            ]);
        }

        $results = [];

        // 1. Produits
        $products = Product::where('name', 'like', "%{$q}%")
            ->orWhere('reference', 'like', "%{$q}%")
            ->limit(5)
            ->get();
        if ($products->count() > 0) {
            $results['Produits'] = $products->map(fn($p) => [
                'title' => $p->name,
                'subtext' => "Réf: {$p->reference} — Stock: {$p->quantity} — " . number_format($p->price_sell, 0, ',', ' ') . " FCFA",
                'url' => route('products.show', $p->id)
            ])->toArray();
        }

        // 2. Clients
        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(5)
            ->get();
        if ($clients->count() > 0) {
            $results['Clients'] = $clients->map(fn($c) => [
                'title' => $c->name,
                'subtext' => "Tél: " . ($c->phone ?? '—') . " — Type: " . ucfirst($c->type) . " — Fidélité: {$c->loyalty_points} pts",
                'url' => route('clients.show', $c->id)
            ])->toArray();
        }

        // 3. Fournisseurs
        $suppliers = Supplier::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(5)
            ->get();
        if ($suppliers->count() > 0) {
            $results['Fournisseurs'] = $suppliers->map(fn($s) => [
                'title' => $s->name,
                'subtext' => "Tél: " . ($s->phone ?? '—') . " — Activité: " . ($s->is_active ? 'Actif' : 'Inactif'),
                'url' => route('suppliers.show', $s->id)
            ])->toArray();
        }

        // 4. Ventes
        $sales = Sale::with('client')
            ->where('id', 'like', "%{$q}%")
            ->orWhere('payment_mode', 'like', "%{$q}%")
            ->orWhereHas('client', function($cq) use ($q) {
                $cq->where('name', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();
        if ($sales->count() > 0) {
            $results['Ventes'] = $sales->map(fn($s) => [
                'title' => "Vente #" . str_pad($s->id, 4, '0', STR_PAD_LEFT),
                'subtext' => "Client: " . ($s->client?->name ?? 'Passager') . " — Total: " . number_format($s->total_amount, 0, ',', ' ') . " FCFA — Paiement: {$s->payment_mode_label}",
                'url' => route('sales.receipt', $s->id)
            ])->toArray();
        }

        // 5. Crédits
        $credits = CreditSale::with('client')
            ->where('id', 'like', "%{$q}%")
            ->orWhereHas('client', function($cq) use ($q) {
                $cq->where('name', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();
        if ($credits->count() > 0) {
            $results['Crédits Clients'] = $credits->map(fn($cr) => [
                'title' => "Crédit Client #" . str_pad($cr->id, 4, '0', STR_PAD_LEFT),
                'subtext' => "Client: {$cr->client->name} — Reste dû: " . number_format($cr->amount_due, 0, ',', ' ') . " FCFA — Statut: " . ucfirst($cr->status),
                'url' => route('credits.show', $cr->id)
            ])->toArray();
        }

        // 6. Bons de Commande Fournisseurs
        $orders = SupplierOrder::with('supplier')
            ->where('reference', 'like', "%{$q}%")
            ->orWhereHas('supplier', function($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();
        if ($orders->count() > 0) {
            $results['Commandes Fournisseurs'] = $orders->map(fn($o) => [
                'title' => $o->reference,
                'subtext' => "Fournisseur: {$o->supplier->name} — Total: " . number_format($o->total_amount, 0, ',', ' ') . " FCFA — Statut: {$o->status_label}",
                'url' => route('advanced_purchases.orders.show', $o->id)
            ])->toArray();
        }

        // 7. Catégories
        $categories = Category::where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get();
        if ($categories->count() > 0) {
            $results['Catégories'] = $categories->map(fn($c) => [
                'title' => $c->name,
                'subtext' => "Description: " . ($c->description ?? 'Aucune'),
                'url' => route('categories.index')
            ])->toArray();
        }

        // 8. Devis
        $quotes = Quote::with('client')
            ->where('reference', 'like', "%{$q}%")
            ->orWhereHas('client', function($cq) use ($q) {
                $cq->where('name', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();
        if ($quotes->count() > 0) {
            $results['Devis'] = $quotes->map(fn($qt) => [
                'title' => $qt->reference,
                'subtext' => "Client: " . ($qt->client?->name ?? '—') . " — Total: " . number_format($qt->total_amount, 0, ',', ' ') . " FCFA",
                'url' => route('quotes.show', $qt->id)
            ])->toArray();
        }

        // 9. Achats (Purchases)
        $purchases = Purchase::with('supplier')
            ->where('reference', 'like', "%{$q}%")
            ->orWhereHas('supplier', function($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();
        if ($purchases->count() > 0) {
            $results['Achats'] = $purchases->map(fn($p) => [
                'title' => $p->reference,
                'subtext' => "Fournisseur: " . ($p->supplier?->name ?? '—') . " — Total: " . number_format($p->total_amount, 0, ',', ' ') . " FCFA",
                'url' => route('purchases.show', $p->id)
            ])->toArray();
        }

        // 10. Utilisateurs (Users)
        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(5)
            ->get();
        if ($users->count() > 0) {
            $results['Utilisateurs'] = $users->map(fn($u) => [
                'title' => $u->name,
                'subtext' => "Email: {$u->email}",
                'url' => route('admin.users.index')
            ])->toArray();
        }

        return response()->json([
            'results' => $results
        ]);
    }
}
