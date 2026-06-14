<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\CreditPayment;
use App\Models\CreditHistory;
use App\Services\ClientScoringService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditSale::with(['client', 'sale']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['en_attente', 'partiel', 'en_retard']);
        }

        $credits = $query->latest()->paginate(20);

        return view('credits.index', compact('credits'));
    }

    public function show(CreditSale $credit)
    {
        $credit->load([
            'client',
            'sale',
            'payments.user',
            'histories.user',
        ]);

        return view('credits.show', compact('credit'));
    }

    public function payment(
        Request $request,
        CreditSale $credit,
        ClientScoringService $scoringService,
        NotificationService $notificationService
    ) {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $credit->amount_due,
            'payment_method' => 'required|in:cash,orange_money,mtn_money,virement',
            'external_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $creditSolded = false;

        DB::transaction(function () use ($request, $credit, $scoringService, $notificationService, &$creditSolded) {
            $amount = (float) $request->amount;
            $internalReference = $this->generateInternalReference($request->payment_method);

            CreditPayment::create([
                'credit_sale_id' => $credit->id,
                'client_id' => $credit->client_id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'payment_method' => $request->payment_method,
                'internal_reference' => $internalReference,
                'external_reference' => $request->external_reference,
                'reference' => $request->external_reference,
                'notes' => $request->notes,
            ]);

            CreditHistory::create([
                'credit_sale_id' => $credit->id,
                'user_id' => auth()->id(),
                'action' => 'payment_added',
                'title' => 'Paiement enregistré',
                'description' => 'Paiement par ' . $this->paymentMethodLabel($request->payment_method),
                'amount' => $amount,
                'meta' => [
                    'payment_method' => $request->payment_method,
                    'internal_reference' => $internalReference,
                    'external_reference' => $request->external_reference,
                ],
            ]);

            $notificationService->notifyManagers(
                'credit_payment',
                'Remboursement crédit enregistré',
                'Un remboursement de ' . number_format($amount, 0, ',', ' ') . ' FCFA a été enregistré pour ' . ($credit->client->name ?? 'un client') . '.',
                route('credits.show', $credit),
                [
                    'credit_id' => $credit->id,
                    'amount' => $amount,
                    'internal_reference' => $internalReference,
                ]
            );

            if ($credit->client && $credit->client->portal_enabled) {
                $notificationService->notifyClient(
                    $credit->client_id,
                    'payment_validated',
                    'Paiement validé',
                    "Votre paiement de " . number_format($amount, 0, ',', ' ') . " FCFA pour votre crédit #" . $credit->id . " a été validé. Merci !",
                    route('client.portal.credits'),
                    ['credit_id' => $credit->id, 'amount' => $amount],
                    'finance'
                );
            }

            $newPaid = (float) $credit->amount_paid + $amount;
            $newDue = max((float) $credit->total_amount - $newPaid, 0);
            $status = $newDue <= 0 ? 'solde' : 'partiel';

            $credit->update([
                'amount_paid' => $newPaid,
                'amount_due' => $newDue,
                'status' => $status,
            ]);

            if ($newDue <= 0) {
                $creditSolded = true;

                CreditHistory::create([
                    'credit_sale_id' => $credit->id,
                    'user_id' => auth()->id(),
                    'action' => 'credit_closed',
                    'title' => 'Crédit soldé',
                    'description' => 'Le crédit a été totalement remboursé.',
                    'amount' => 0,
                ]);

                $notificationService->notifyManagers(
                    'credit_closed',
                    'Crédit soldé',
                    'Le crédit de ' . ($credit->client->name ?? 'un client') . ' a été totalement remboursé.',
                    route('credits.show', $credit),
                    [
                        'credit_id' => $credit->id,
                    ]
                );

                if ($credit->client && $credit->client->portal_enabled) {
                    $notificationService->notifyClient(
                        $credit->client_id,
                        'credit_closed',
                        'Crédit soldé',
                        "Félicitations, votre crédit #" . $credit->id . " a été entièrement soldé.",
                        route('client.portal.credits'),
                        ['credit_id' => $credit->id],
                        'finance'
                    );
                }
            }

            $scoringService->update($credit->client);
        });

        $toasts = [
            [
                'type' => 'success',
                'title' => 'Remboursement enregistré',
                'message' => 'Le remboursement crédit a été enregistré avec succès.',
                'sound' => true,
            ],
        ];

        if ($creditSolded) {
            $toasts[] = [
                'type' => 'success',
                'title' => 'Crédit soldé',
                'message' => 'Ce crédit est maintenant totalement remboursé.',
                'sound' => true,
            ];
        }

        return redirect()
            ->route('credits.show', $credit)
            ->with('success', 'Paiement enregistré avec succès.')
            ->with('toast_notifications', $toasts);
    }

    public function paymentsHistory()
    {
        $payments = CreditPayment::with(['client', 'creditSale', 'user'])
            ->latest()
            ->paginate(20);

        return view('credits.payments-history', compact('payments'));
    }

    private function generateInternalReference(string $method): string
    {
        $prefix = match ($method) {
            'orange_money' => 'OM',
            'mtn_money' => 'MOMO',
            'virement' => 'VIR',
            default => 'CASH',
        };

        $date = now()->format('Ymd');

        $count = CreditPayment::where('payment_method', $method)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $prefix . '-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function paymentMethodLabel(string $method): string
    {
        return match ($method) {
            'orange_money' => 'Orange Money',
            'mtn_money' => 'MTN Mobile Money',
            'virement' => 'Virement bancaire',
            default => 'Espèces',
        };
    }
}
