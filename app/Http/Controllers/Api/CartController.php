<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    public function getCartItems()
    {
        $userId = auth()->id();


        if (DB::table('shoppingcart')->where('identifier', $userId)->exists()) {
            Cart::instance('shopping')->restore($userId);
        }

        return response()->json([
            'status' => true,
            'messages'=>[__('messages.offer_carts') => Cart::instance('shopping')->content()]
        ]);
    }

    public function addToCart(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        $userId = auth()->id();
        $product = Product::find($request->product_id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }
        $cartItem = Cart::instance('shopping')->search(fn($cartItem) => $cartItem->id == $request->product_id)->first();

        if ($cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Product is already in the cart. You cannot add the same product twice.'
            ], 400);
        }

        $newCartItem = Cart::instance('shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => $request->quantity,
            'price' => $request->price,
            'weight' => $request->weight,
            'options' => ['image' => $product->image ?? null]
        ]);

        Cart::instance('shopping')->store($userId);

        return response()->json([
            'status' => true,
            'message' => __('messages.add_cart'),
            'cart_item' => $newCartItem,
            'total'=>Cart::total()
        ]);
    }
    public function updateCartItem(Request $request, $rowId)
    {
        $validator = validator()->make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }

        $userId = auth()->id();

        if (DB::table('shoppingcart')->where('identifier', $userId)->exists()) {
            Cart::instance('shopping')->restore($userId);
        }

        $cartItem = Cart::instance('shopping')->get($rowId);

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => "The cart does not contain an item with rowId: $rowId."
            ], 404);
        }

        Cart::instance('shopping')->update($rowId, ['qty' => $request->quantity]);
        return response()->json([
            'status' => true,
            'message' => __('messages.update_cart'),
            'cart_item' => Cart::instance('shopping')->get($rowId)
        ]);
    }
    public function removeCartItem($rowId)
    {
        $userId = auth()->id();

        if (DB::table('shoppingcart')->where('identifier', $userId)->exists()) {
            Cart::instance('shopping')->restore($userId);
        }

        // جلب العنصر من السلة باستخدام `rowId`
        $cartItem = Cart::instance('shopping')->get($rowId);

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => "The cart does not contain an item with rowId: $rowId."
            ], 404);
        }

        // حذف العنصر
        Cart::instance('shopping')->remove($rowId);

        // إعادة تخزين السلة بعد الحذف
        Cart::instance('shopping')->store($userId);

        return response()->json([
            'status' => true,
            'message' => __('messages.delete_cart')
        ]);
    }

    public function clearCart()
    {
        Cart::instance('shopping')->destroy();
        Cart::instance('shopping')->store(auth()->id());

        return response()->json([
            'status' => true,
            'message' => __('messages.delete_cart')
        ]);
    }
}
