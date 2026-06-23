<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Platform\SubscriptionPayment;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index()
    {
        $payments = SubscriptionPayment::with(['tenant', 'subscription.plan'])->latest()->paginate(15);
        return view('landlord.payments.index', compact('payments'));
    }
}
