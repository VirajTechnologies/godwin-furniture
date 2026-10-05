<?php

use App\Http\Controllers\Store\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('store.home');
Route::get('catalog', [HomeController::class, 'catalog'])->name('store.catalog');
Route::get('product/{slug}', [HomeController::class, 'product'])->name('store.product');
Route::get('cart', [HomeController::class, 'cart'])->name('store.cart');
Route::get('login', [HomeController::class, 'login'])->name('store.login');
Route::get('register', [HomeController::class, 'register'])->name('store.register');

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
