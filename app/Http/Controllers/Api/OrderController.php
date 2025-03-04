<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Gloudemans\Shoppingcart\Facades\Cart;

class OrderController extends Controller
{


    public function createOrder(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'delivery_address' => 'required|string',
            'city_name' => 'required|string',
            'address_name' => 'required|string',
            'building_number' => 'required|string',
            'payment_method' => 'required|string|in:stripe,paypal',
        ]);

        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        $user = Auth::user();
        Cart::instance('shopping')->restore($user->id);
        $cartItems = Cart::instance('shopping')->content();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'there is no items in cart'], 400);
        }

        $order = Order::create([
            'order_number' => Str::random(10),
            'user_id' => $user->id,
            'delivery_address' => json_encode([
                'city_name' => $request->city_name,
                'address_name' => $request->address_name,
                'building_number' => $request->building_number,
            ]),
            'payment_method' => $request->payment_method,
            'payment_status' => 'paid',
            'status' => 'shipped',
        ]);

        foreach ($cartItems as $item) {
            $product = Product::find($item->id);
            if ($product) {
                $order->products()->attach($product, [
                    'quantity' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->qty * $item->price,
                ]);
            }
        }

        if ($request->payment_method == 'paypal') {
            $paypalRequest = new Request([
                'total' => Cart::instance('shopping')->total(),
            ]);
            return app(PayPalController::class)->createPayment($paypalRequest);
        }
        if ($request->payment_method == 'stripe') {
            $stripe = new Request([
                'total' => Cart::instance('shopping')->total()
            ]);
            return app(StripeController::class)->createPayment($stripe);
        }
        Cart::destroy();
        return response()->json(['message' => __('messages.add_order'), 'order' => $order], 201);
    }

    public function getUserOrders()
    {
        $orders = Auth::user()->orders;
        return response()->json(['message'=>__('messages.offer_orders'),$orders]);
    }
    public function getOrderById($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order);
    }
    public function updateOrderStatus(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'status' => 'required|in:paid,not_paid',
        ]);
        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        $order = Order::findOrFail($id);
        $order->save();
        return response()->json(['message' => __('messages.update_order')]);
    }
}
