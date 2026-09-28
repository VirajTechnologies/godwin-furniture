<?php

use App\Http\Controllers\Branch\Auth\LoginController;
use App\Http\Controllers\Branch\HomeController;
use App\Http\Controllers\Branch\SaleController;
use App\Http\Controllers\Branch\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('branch')->name('branch.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'branch_user'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('sales/{order}', [SaleController::class, 'show'])->name('sales.show');

        Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    });
});
