<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats du jour
        $caJour = Sale::whereDate('created_at', today())
                      ->where('status', '!=', 'annulee')
                      ->sum('total_amount');

        $ventesJour = Sale::whereDate('created_at', today())
                          ->where('status', '!=', 'annulee')
                          ->count();

        // Stats stock
        $totalProduits  = Product::where('is_active', true)->count();
        $alertesStock   = Product::where('is_active', true)
                                 ->whereRaw('quantity <= alert_threshold')
                                 ->count();

        // Ventes 7 derniers jours pour graphe
        $ventesGraph = Sale::where('status', '!=', 'annulee')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as nb')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Remplir les jours manquants
        $labels  = [];
        $totals  = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $day  = now()->subDays($i)->locale('fr')->isoFormat('DD/MM');
            $labels[] = $day;
            $found = $ventesGraph->firstWhere('date', $date);
            $totals[] = $found ? (float) $found->total : 0;
        }

        // Dernières ventes
        $dernieresVentes = Sale::with(['user', 'client', 'details'])
                               ->orderBy('created_at', 'desc')
                               ->limit(8)
                               ->get();

        // Top produits du mois
        $topProduits = DB::table('sale_details')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->where('sales.status', '!=', 'annulee')
            ->whereMonth('sales.created_at', now()->month)
            ->select(
                'products.name',
                DB::raw('SUM(sale_details.quantity) as total_qty'),
                DB::raw('SUM(sale_details.subtotal) as total_ca')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_ca')
            ->limit(5)
            ->get();

        // Produits en alerte
        $produitsAlerte = Product::with('category')
                                 ->where('is_active', true)
                                 ->whereRaw('quantity <= alert_threshold')
                                 ->orderBy('quantity')
                                 ->limit(5)
                                 ->get();

        return view('dashboard', compact(
            'caJour', 'ventesJour', 'totalProduits', 'alertesStock',
            'labels', 'totals', 'dernieresVentes', 'topProduits', 'produitsAlerte'
        ));
    }
}