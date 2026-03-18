<?php

use App\Enums\RolesEnum;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeConnectController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\VendorController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');

Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/s/{vendor:store_name}', [VendorController::class, 'profile'])
    ->name('vendor.profile');

Route::get('/d/{department:slug}/products', [ProductController::class, 'byDepartment'])
    ->name('product.byDepartment');

Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')
        ->name('cart.index');
    Route::get('/cart/items', 'items')
        ->name('cart.items');
    Route::post('/cart/add/{product}', 'store')
        ->name('cart.store');
    Route::put('/cart/{product}', 'update')
        ->name('cart.update');
    Route::delete('/cart/{product:id}', 'destroy')
        ->name('cart.destroy');
});

Route::middleware('verified')->group(function () {
    Route::post('/cart/checkout', [CartController::class, 'checkout'])
        ->name('cart.checkout');

    Route::get('/stripe/success', [StripeController::class, 'success'])
        ->name('stripe.success');

    Route::get('/stripe/order-status', [StripeController::class, 'orderStatus'])
        ->name('stripe.order.status');

    Route::get('/stripe/cancel', [StripeController::class, 'cancel'])
        ->name('stripe.cancel');

    Route::get('/stripe/connect', [StripeConnectController::class, 'connect'])
        ->name('stripe.connect')
        ->middleware('role:'.RolesEnum::Vendor->value);

    Route::get('/stripe/return', [StripeConnectController::class, 'handleReturn'])
        ->name('stripe.return');

    Route::get('/stripe/refresh', [StripeConnectController::class, 'refresh'])
        ->name('stripe.refresh');

});

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->name('stripe.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
