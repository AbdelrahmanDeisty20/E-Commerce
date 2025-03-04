<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayPalController extends Controller
{
    public function createPayment(Request $request)
    {
        $provider = new PayPalClient();
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $totalAmount = $request->total ?? 0;

        if ($totalAmount <= 0) {
            return response()->json(['message' => 'The amount is invalid'], 400);
        }
        $refrenceId= Str::random(10);
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel'),
            ],
            "purchase_units" => [
                [
                    "reference_id" => "default",
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $totalAmount,
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && !empty($response['links'])) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return response()->json([
                        'message' => 'order created successfully',
                        'paypal_url' => 'link: ', $link['href']
                    ]);
                }
            }
        }

        return response()->json(['message' => 'something is wrong  with paypal'], 500);
    }

    public function paymentSuccess(Request $request)
    {
        $provider = new PayPalClient();
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        $response = $provider->capturePaymentOrder($request->token);
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return response()->json([
                'message' => 'order paied successfully',
                'transecation_id' => $order->id ?? null,
                'total_paid' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null,
                'currency' => $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? 'USD',
            ]);

        }
        return response()->json(['message' => 'something wrong in payment'], 500);
    }
    public function paymentCancel()
    {
        return response()->json(['message' => 'paymenet canceld'], 400);
    }
}
