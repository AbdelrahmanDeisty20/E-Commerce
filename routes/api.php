<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PayPalController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\AutoCheckPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum', 'permission', 'verified')->group(function () {
    Route::get('user', [AuthController::class, 'user']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::apiResource('products', ProductController::class);
    Route::post('cart', [CartController::class, 'addToCart']);
    Route::get('cart', [CartController::class, 'getCartItems']);
    Route::put('cart/{rowId}', [CartController::class, 'updateCartItem']);
    Route::delete('cart/{rowId}', [CartController::class, 'removeCartItem']);
    Route::post('cart/clear', [CartController::class, 'clearCart']);
    Route::apiResource('categories', CategoryController::class);
    Route::post('/orders', [OrderController::class, 'createOrder']);  // إنشاء الطلب
    Route::get('/orders', [OrderController::class, 'getUserOrders']);  // استرجاع الطلبات
    Route::get('/orders/{id}', [OrderController::class, 'getOrderById']);  // استرجاع تفاصيل الطلب
    Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);
});
Route::get('/paypal/success', [PayPalController::class, 'paymentSuccess'])->name('paypal.success');
Route::get('/paypal/cancel', [PayPalController::class, 'paymentCancel'])->name('paypal.cancel');

