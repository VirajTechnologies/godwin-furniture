<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchStockController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\StockRequestController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\WarehouseStockController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'super_admin'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('reports/sales-by-branch', [ReportController::class, 'salesByBranch'])->name('reports.sales-by-branch');
        Route::get('reports/sales-by-product', [ReportController::class, 'salesByProduct'])->name('reports.sales-by-product');
        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/transfers', [ReportController::class, 'transfers'])->name('reports.transfers');
        Route::get('reports/stock-requests', [ReportController::class, 'stockRequests'])->name('reports.stock-requests');

        Route::get('states', [StateController::class, 'index'])->name('states.index');
        Route::get('states/create', [StateController::class, 'create'])->name('states.create');
        Route::post('states', [StateController::class, 'store'])->name('states.store');
        Route::get('states/{state}/edit', [StateController::class, 'edit'])->name('states.edit');
        Route::put('states/{state}', [StateController::class, 'update'])->name('states.update');
        Route::post('states/{state}/status', [StateController::class, 'updateStatus'])->name('states.status');

        Route::get('districts/options', [DistrictController::class, 'options'])->name('districts.options');
        Route::get('districts', [DistrictController::class, 'index'])->name('districts.index');
        Route::get('districts/create', [DistrictController::class, 'create'])->name('districts.create');
        Route::post('districts', [DistrictController::class, 'store'])->name('districts.store');
        Route::get('districts/{district}/edit', [DistrictController::class, 'edit'])->name('districts.edit');
        Route::put('districts/{district}', [DistrictController::class, 'update'])->name('districts.update');
        Route::post('districts/{district}/status', [DistrictController::class, 'updateStatus'])->name('districts.status');

        Route::get('cities/options', [CityController::class, 'options'])->name('cities.options');
        Route::get('cities', [CityController::class, 'index'])->name('cities.index');
        Route::get('cities/create', [CityController::class, 'create'])->name('cities.create');
        Route::post('cities', [CityController::class, 'store'])->name('cities.store');
        Route::get('cities/{city}/edit', [CityController::class, 'edit'])->name('cities.edit');
        Route::put('cities/{city}', [CityController::class, 'update'])->name('cities.update');
        Route::post('cities/{city}/status', [CityController::class, 'updateStatus'])->name('cities.status');

        Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.status');

        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::post('warehouses/{warehouse}/primary', [WarehouseController::class, 'makePrimary'])->name('warehouses.primary');
        Route::post('warehouses/{warehouse}/status', [WarehouseController::class, 'updateStatus'])->name('warehouses.status');

        Route::get('branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('branches', [BranchController::class, 'store'])->name('branches.store');
        Route::get('branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::post('branches/{branch}/status', [BranchController::class, 'updateStatus'])->name('branches.status');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('categories/{category}/status', [CategoryController::class, 'updateStatus'])->name('categories.status');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('products/{product}/status', [ProductController::class, 'updateStatus'])->name('products.status');

        Route::get('stocks', [WarehouseStockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/create', [WarehouseStockController::class, 'create'])->name('stocks.create');
        Route::post('stocks', [WarehouseStockController::class, 'store'])->name('stocks.store');
        Route::get('stocks/{stock}/edit', [WarehouseStockController::class, 'edit'])->name('stocks.edit');
        Route::put('stocks/{stock}', [WarehouseStockController::class, 'update'])->name('stocks.update');

        Route::get('branch-stocks', [BranchStockController::class, 'index'])->name('branch-stocks.index');

        Route::get('stock-requests', [StockRequestController::class, 'index'])->name('stock-requests.index');
        Route::get('stock-requests/{stockRequest}', [StockRequestController::class, 'show'])->name('stock-requests.show');
        Route::post('stock-requests/{stockRequest}/transfer', [StockRequestController::class, 'createTransfer'])->name('stock-requests.transfer');

        Route::get('transfers', [StockTransferController::class, 'index'])->name('transfers.index');
        Route::get('transfers/create', [StockTransferController::class, 'create'])->name('transfers.create');
        Route::post('transfers', [StockTransferController::class, 'store'])->name('transfers.store');
        Route::get('transfers/{transfer}/edit', [StockTransferController::class, 'edit'])->name('transfers.edit');
        Route::put('transfers/{transfer}', [StockTransferController::class, 'update'])->name('transfers.update');
        Route::post('transfers/{transfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('transfers.dispatch');
        Route::post('transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('transfers.cancel');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('orders/{order}/dispatch', [OrderController::class, 'dispatchOrder'])->name('orders.dispatch');
        Route::post('orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    });
});
