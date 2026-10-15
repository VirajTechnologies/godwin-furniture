<?php

use App\Http\Controllers\Agent\Auth\LoginController;
use App\Http\Controllers\Agent\HomeController;
use App\Http\Controllers\Agent\SaleController;
use App\Http\Controllers\Agent\StockController;
use App\Http\Controllers\Agent\StockRequestController;
use App\Http\Controllers\Agent\TransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('agent')->name('agent.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'agent'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::get('customers/lookup', [SaleController::class, 'lookupCustomer'])->name('customers.lookup');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
        Route::get('sales/{order}', [SaleController::class, 'show'])->name('sales.show');

        Route::get('stock', [StockController::class, 'index'])->name('stock.index');

        Route::get('stock-requests', [StockRequestController::class, 'index'])->name('stock-requests.index');
        Route::get('stock-requests/create', [StockRequestController::class, 'create'])->name('stock-requests.create');
        Route::post('stock-requests', [StockRequestController::class, 'store'])->name('stock-requests.store');
        Route::get('stock-requests/{stockRequest}', [StockRequestController::class, 'show'])->name('stock-requests.show');
        Route::post('stock-requests/{stockRequest}/cancel', [StockRequestController::class, 'cancel'])->name('stock-requests.cancel');

        Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');
        Route::post('transfers/{transfer}/complete', [TransferController::class, 'complete'])->name('transfers.complete');
        Route::get('transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
    });
});
