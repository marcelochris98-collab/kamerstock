<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Client;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CreditSale;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function export($type)
    {
        $user = auth()->user();
        switch ($type) {
            case 'products':
                if (!$user->hasPermission('products.view')) abort(403, 'Accès non autorisé.');
                break;
            case 'clients':
            case 'credits':
                if (!$user->hasPermission('clients.view')) abort(403, 'Accès non autorisé.');
                break;
            case 'suppliers':
                if (!$user->hasPermission('suppliers.view')) abort(403, 'Accès non autorisé.');
                break;
            case 'sales':
                if (!$user->hasPermission('sales.view')) abort(403, 'Accès non autorisé.');
                break;
            case 'purchases':
                if (!$user->hasPermission('purchases.view')) abort(403, 'Accès non autorisé.');
                break;
            default:
                abort(404);
        }

        return new StreamedResponse(function () use ($type) {
            $handle = fopen('php://output', 'w');
            
            // Ajouter le BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            switch ($type) {
                case 'products':
                    fputcsv($handle, ['ID', 'Référence', 'Nom', 'Catégorie', 'Fournisseur', 'Unité', 'Prix Achat', 'Prix Vente', 'Stock', 'Seuil Alerte']);
                    Product::with(['category', 'supplier'])->chunk(100, function ($products) use ($handle) {
                        foreach ($products as $p) {
                            fputcsv($handle, [
                                $p->id,
                                $p->reference,
                                $p->name,
                                $p->category?->name ?? '—',
                                $p->supplier?->name ?? '—',
                                $p->unit_label,
                                $p->price_buy,
                                $p->price_sell,
                                $p->quantity,
                                $p->alert_threshold
                            ]);
                        }
                    });
                    break;

                case 'clients':
                    fputcsv($handle, ['ID', 'Nom', 'Téléphone', 'Email', 'Type', 'Points Fidélité', 'Crédit Disponible', 'Date Création']);
                    Client::chunk(100, function ($clients) use ($handle) {
                        foreach ($clients as $c) {
                            fputcsv($handle, [
                                $c->id,
                                $c->name,
                                $c->phone ?? '—',
                                $c->email ?? '—',
                                ucfirst($c->type),
                                $c->loyalty_points,
                                $c->credit_available,
                                $c->created_at->format('Y-m-d H:i')
                            ]);
                        }
                    });
                    break;

                case 'suppliers':
                    fputcsv($handle, ['ID', 'Nom', 'Téléphone', 'Email', 'Adresse', 'Personne Contact', 'Actif']);
                    Supplier::chunk(100, function ($suppliers) use ($handle) {
                        foreach ($suppliers as $s) {
                            fputcsv($handle, [
                                $s->id,
                                $s->name,
                                $s->phone ?? '—',
                                $s->email ?? '—',
                                $s->address ?? '—',
                                $s->contact_person ?? '—',
                                $s->is_active ? 'Oui' : 'Non'
                            ]);
                        }
                    });
                    break;

                case 'sales':
                    fputcsv($handle, ['ID Vente', 'Caissier', 'Client', 'Total (FCFA)', 'Montant Payé (FCFA)', 'Monnaie Rendue (FCFA)', 'Remise (FCFA)', 'Mode de Paiement', 'Statut', 'Date']);
                    Sale::with(['user', 'client'])->chunk(100, function ($sales) use ($handle) {
                        foreach ($sales as $s) {
                            fputcsv($handle, [
                                $s->id,
                                $s->user->name,
                                $s->client?->name ?? 'Passager',
                                $s->total_amount,
                                $s->amount_paid,
                                $s->change_due,
                                $s->discount,
                                $s->payment_mode_label,
                                $s->status_label,
                                $s->created_at->format('Y-m-d H:i')
                            ]);
                        }
                    });
                    break;

                case 'purchases':
                    fputcsv($handle, ['Réf Achat', 'Caissier', 'Fournisseur', 'Total (FCFA)', 'Montant Payé (FCFA)', 'Reste Dû (FCFA)', 'Statut', 'Date']);
                    Purchase::with(['user', 'supplier'])->chunk(100, function ($purchases) use ($handle) {
                        foreach ($purchases as $p) {
                            fputcsv($handle, [
                                $p->reference,
                                $p->user->name,
                                $p->supplier?->name ?? '—',
                                $p->total_amount,
                                $p->amount_paid,
                                $p->amount_due,
                                $p->status,
                                $p->purchase_date->format('Y-m-d')
                            ]);
                        }
                    });
                    break;

                case 'credits':
                    fputcsv($handle, ['ID Crédit', 'Client', 'N° Vente', 'Montant Total (FCFA)', 'Payé (FCFA)', 'Reste Dû (FCFA)', 'Statut', 'Date Création']);
                    CreditSale::with(['client', 'sale'])->chunk(100, function ($credits) use ($handle) {
                        foreach ($credits as $cr) {
                            fputcsv($handle, [
                                $cr->id,
                                $cr->client->name,
                                $cr->sale_id,
                                $cr->total_amount,
                                $cr->amount_paid,
                                $cr->amount_due,
                                $cr->status,
                                $cr->created_at->format('Y-m-d H:i')
                            ]);
                        }
                    });
                    break;
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="export_' . $type . '_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }
}
