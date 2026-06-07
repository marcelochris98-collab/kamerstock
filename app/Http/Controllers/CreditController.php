<?php

namespace App\Http\Controllers;

use App\Models\CreditSale;
use App\Models\CreditPayment;
use App\Services\ClientScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function index()
    {
        $credits = CreditSale::with(['client', 'sale'])
            ->whereIn('status', ['en_attente', 'partiel', 'en_retard'])
            ->latest()
            ->paginate(20);

        return view('credits.index', compact('credits'));
    }

    public function show(CreditSale $credit)
    {
        $credit->load(['client', 'sale', 'payments.user']);

        return view('credits.show', compact('credit'));
    }

    public function payment(Request $request, CreditSale $credit, ClientScoringService $scoringService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $credit->amount_due,
            'payment_method' => 'required|in:cash,orange_money,mtn_money,virement',
           'external_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $credit, $scoringService) {
            $amount = (float) $request->amount;
CreditPayment::create([
    'credit_sale_id' => $credit->id,
    'client_id' => $credit->client_id,
    'user_id' => auth()->id(),
    'amount' => $amount,
    'payment_method' => $request->payment_method,
    'internal_reference' => $this->generateInternalReference($request->payment_method),
    'external_reference' => $request->external_reference,
    'reference' => $request->external_reference,
    'notes' => $request->notes,
]);

            $newPaid = (float) $credit->amount_paid + $amount;
            $newDue = max((float) $credit->total_amount - $newPaid, 0);

            $status = $newDue <= 0 ? 'solde' : 'partiel';

            $credit->update([
                'amount_paid' => $newPaid,
                'amount_due' => $newDue,
                'status' => $status,
            ]);

            $scoringService->update($credit->client);
        });

        return redirect()
            ->route('credits.show', $credit)
            ->with('success', 'Paiement enregistré avec succès.');
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
         public function paymentsHistory()
{
    $payments = \App\Models\CreditPayment::with(['client', 'creditSale', 'user'])
        ->latest()
        ->paginate(20);

    return view('credits.payments-history', compact('payments'));
}
}