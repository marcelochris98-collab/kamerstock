<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Client;
use App\Models\CreditSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : now()->subDays(30)->toDateString();
        $endDate = $request->filled('end_date') ? $request->end_date : now()->toDateString();

        // 1. Chiffre d'Affaires (CA)
        $totalSales = Sale::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', '!=', 'annulee')
            ->sum('total_amount');

        $salesCount = Sale::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', '!=', 'annulee')
            ->count();

        // 2. Achats & Dettes
        $totalPurchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->sum('total_amount');

        $totalSupplierDebt = Purchase::sum('amount_due');
        $totalClientDebt = CreditSale::whereIn('status', ['en_attente', 'partiel', 'en_retard'])->sum('amount_due');

        // 3. Calcul de la Marge & Coût des Ventes (COGS)
        $saleDetails = SaleDetail::with('product')
            ->whereHas('sale', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->where('status', '!=', 'annulee');
            })->get();

        $cogs = 0;
        $revenue = 0;
        foreach ($saleDetails as $detail) {
            $costPrice = $detail->product ? (float) $detail->product->price_buy : (float) $detail->unit_price;
            $cogs += $costPrice * $detail->quantity;
            $revenue += $detail->subtotal;
        }

        $margin = max(0, $revenue - $cogs);
        $marginPercentage = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

        // 4. Valorisation du Stock actuel
        $stockValuationBuy = Product::where('is_active', true)->sum(DB::raw('quantity * price_buy'));
        $stockValuationSell = Product::where('is_active', true)->sum(DB::raw('quantity * price_sell'));
        $totalStockItems = Product::where('is_active', true)->sum('quantity');

        // 5. Répartition des ventes par mode de paiement
        $paymentModes = Sale::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', '!=', 'annulee')
            ->select('payment_mode', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_mode')
            ->get();

        // 6. Données journalières pour le graphique (lightweight bar chart)
        $dailySales = Sale::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', '!=', 'annulee')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('reports.index', compact(
            'startDate',
            'endDate',
            'totalSales',
            'salesCount',
            'totalPurchases',
            'totalSupplierDebt',
            'totalClientDebt',
            'cogs',
            'margin',
            'marginPercentage',
            'stockValuationBuy',
            'stockValuationSell',
            'totalStockItems',
            'paymentModes',
            'dailySales'
        ));
    }
}
