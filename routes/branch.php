<?php

use App\Http\Controllers\Branch\Auth\LoginController;
use App\Http\Controllers\Branch\HomeController;
use App\Http\Controllers\Branch\ReportController;
use App\Http\Controllers\Branch\SaleController;
use App\Http\Controllers\Branch\StockController;
use App\Http\Controllers\Branch\StockRequestController;
use App\Http\Controllers\Branch\TransferController;
use Illuminate\Support\Facades\Route;

Route::prefix('branch')->name('branch.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'branch_user'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [HomeController::class, 'index'])->name('home');

        Route::get('reports/sales-by-product', [ReportController::class, 'salesByProduct'])->name('reports.sales-by-product');
        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/transfers', [ReportController::class, 'transfers'])->name('reports.transfers');
        Route::get('reports/stock-requests', [ReportController::class, 'stockRequests'])->name('reports.stock-requests');

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
        Route::post('transfers/{transfer}/receive', [TransferController::class, 'receive'])->name('transfers.receive');
        Route::get('transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
    });
});
