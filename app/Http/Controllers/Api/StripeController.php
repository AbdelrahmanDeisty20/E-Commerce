<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Charge;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createPayment(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric|min:1',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));
        $totalAmount = $request->total ?? 0;
        $stripeToken = 'tok_visa';
        $charge = Charge::create([
            "amount" => $totalAmount * 100,
            "currency" => "usd",
            "source" => $stripeToken,
            "description" => "payment success"
        ]);

        return response()->json([
            'message' => 'paid successfully',
            'charge_id' => $charge->id
        ]);
    }
}
