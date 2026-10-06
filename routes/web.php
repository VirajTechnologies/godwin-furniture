<?php

use App\Http\Controllers\Store\AuthController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('store.home');
Route::get('catalog', [HomeController::class, 'catalog'])->name('store.catalog');
Route::get('product/{slug}', [HomeController::class, 'product'])->name('store.product');
Route::get('cart', [CartController::class, 'show'])->name('store.cart');
Route::post('cart/items', [CartController::class, 'add'])->name('store.cart.add');
Route::patch('cart/items/{product}', [CartController::class, 'update'])->name('store.cart.update');
Route::delete('cart/items/{product}', [CartController::class, 'remove'])->name('store.cart.remove');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('store.login');
    Route::post('login', [AuthController::class, 'login'])->name('store.login.store');
    Route::get('register', [AuthController::class, 'showRegister'])->name('store.register');
    Route::post('register', [AuthController::class, 'register'])->name('store.register.store');
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('store.logout');

Route::middleware('auth')->group(function () {
    Route::get('account', [ProfileController::class, 'show'])->name('store.account');
    Route::put('account', [ProfileController::class, 'update'])->name('store.account.update');
    Route::put('account/password', [ProfileController::class, 'updatePassword'])->name('store.account.password');
    Route::post('account/addresses', [ProfileController::class, 'storeAddress'])->name('store.account.addresses.store');
    Route::put('account/addresses/{address}/default', [ProfileController::class, 'makeDefaultAddress'])->name('store.account.addresses.default');
    Route::delete('account/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('store.account.addresses.destroy');
    Route::get('checkout', [CheckoutController::class, 'show'])->name('store.checkout');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('store.checkout.store');
    Route::get('orders', [OrderController::class, 'index'])->name('store.orders.index');
    Route::get('orders/{order:code}', [OrderController::class, 'show'])->name('store.orders.show');
});

Route::get('/csrf-token', function () {
    // Touch the guard so a remembered login is written back into a new session
    // after the idle session has expired, before the fresh token is issued.
    auth()->user();

    return response()
        ->json([
            'token' => csrf_token(),
            'authenticated' => auth()->check(),
        ])
        ->header('Cache-Control', 'no-store, private');
})->middleware('throttle:60,1')->name('csrf.token');

require __DIR__.'/admin.php';
require __DIR__.'/branch.php';
