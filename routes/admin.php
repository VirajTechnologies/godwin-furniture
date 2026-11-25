<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchStockController;
use App\Http\Controllers\Admin\BusinessGroupController;
use App\Http\Controllers\Admin\CatalogConfigOptionController;
use App\Http\Controllers\Admin\CatalogProductCategoryController;
use App\Http\Controllers\Admin\CatalogRoomController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignTypeController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductColourController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductFinishController;
use App\Http\Controllers\Admin\ProductModelController;
use App\Http\Controllers\Admin\ProductUpholsteryController;
use App\Http\Controllers\Admin\ProjectCategoryController;
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

        Route::get('reports', [ReportController::class, 'overview'])->name('reports.overview');
        Route::get('reports/export', [ReportController::class, 'exportOverview'])->name('reports.export');
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

        Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
        Route::get('agents/create', [AgentController::class, 'create'])->name('agents.create');
        Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
        Route::post('agents/{agent}/cashiers', [AgentController::class, 'storeCashier'])->name('agents.cashiers.store');
        Route::put('agents/{agent}/cashiers/{cashier}', [AgentController::class, 'updateCashier'])->name('agents.cashiers.update');
        Route::get('agents/{agent}/edit', [AgentController::class, 'edit'])->name('agents.edit');
        Route::put('agents/{agent}', [AgentController::class, 'update'])->name('agents.update');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        Route::get('business-groups', [BusinessGroupController::class, 'index'])->name('business-groups.index');
        Route::get('business-groups/create', [BusinessGroupController::class, 'create'])->name('business-groups.create');
        Route::post('business-groups', [BusinessGroupController::class, 'store'])->name('business-groups.store');
        Route::get('business-groups/{business_group}/edit', [BusinessGroupController::class, 'edit'])->name('business-groups.edit');
        Route::put('business-groups/{business_group}', [BusinessGroupController::class, 'update'])->name('business-groups.update');
        Route::post('business-groups/{business_group}/status', [BusinessGroupController::class, 'updateStatus'])->name('business-groups.status');

        Route::get('project-categories', [ProjectCategoryController::class, 'index'])->name('project-categories.index');
        Route::get('project-categories/create', [ProjectCategoryController::class, 'create'])->name('project-categories.create');
        Route::post('project-categories', [ProjectCategoryController::class, 'store'])->name('project-categories.store');
        Route::get('project-categories/{project_category}/edit', [ProjectCategoryController::class, 'edit'])->name('project-categories.edit');
        Route::put('project-categories/{project_category}', [ProjectCategoryController::class, 'update'])->name('project-categories.update');
        Route::post('project-categories/{project_category}/status', [ProjectCategoryController::class, 'updateStatus'])->name('project-categories.status');

        Route::get('catalog-rooms', [CatalogRoomController::class, 'index'])->name('catalog-rooms.index');
        Route::get('catalog-rooms/create', [CatalogRoomController::class, 'create'])->name('catalog-rooms.create');
        Route::post('catalog-rooms', [CatalogRoomController::class, 'store'])->name('catalog-rooms.store');
        Route::get('catalog-rooms/{catalog_room}/edit', [CatalogRoomController::class, 'edit'])->name('catalog-rooms.edit');
        Route::put('catalog-rooms/{catalog_room}', [CatalogRoomController::class, 'update'])->name('catalog-rooms.update');
        Route::post('catalog-rooms/{catalog_room}/status', [CatalogRoomController::class, 'updateStatus'])->name('catalog-rooms.status');

        Route::get('catalog-product-categories', [CatalogProductCategoryController::class, 'index'])->name('catalog-product-categories.index');
        Route::get('catalog-product-categories/create', [CatalogProductCategoryController::class, 'create'])->name('catalog-product-categories.create');
        Route::post('catalog-product-categories', [CatalogProductCategoryController::class, 'store'])->name('catalog-product-categories.store');
        Route::get('catalog-product-categories/{catalog_product_category}/edit', [CatalogProductCategoryController::class, 'edit'])->name('catalog-product-categories.edit');
        Route::put('catalog-product-categories/{catalog_product_category}', [CatalogProductCategoryController::class, 'update'])->name('catalog-product-categories.update');
        Route::post('catalog-product-categories/{catalog_product_category}/status', [CatalogProductCategoryController::class, 'updateStatus'])->name('catalog-product-categories.status');

        Route::get('design-types', [DesignTypeController::class, 'index'])->name('design-types.index');
        Route::get('design-types/create', [DesignTypeController::class, 'create'])->name('design-types.create');
        Route::post('design-types', [DesignTypeController::class, 'store'])->name('design-types.store');
        Route::get('design-types/{design_type}/edit', [DesignTypeController::class, 'edit'])->name('design-types.edit');
        Route::put('design-types/{design_type}', [DesignTypeController::class, 'update'])->name('design-types.update');
        Route::post('design-types/{design_type}/status', [DesignTypeController::class, 'updateStatus'])->name('design-types.status');

        Route::get('product-models', [ProductModelController::class, 'index'])->name('product-models.index');
        Route::get('product-models/create', [ProductModelController::class, 'create'])->name('product-models.create');
        Route::post('product-models', [ProductModelController::class, 'store'])->name('product-models.store');
        Route::get('product-models/{product_model}/edit', [ProductModelController::class, 'edit'])->name('product-models.edit');
        Route::put('product-models/{product_model}', [ProductModelController::class, 'update'])->name('product-models.update');
        Route::post('product-models/{product_model}/status', [ProductModelController::class, 'updateStatus'])->name('product-models.status');

        Route::get('catalog-config-options', [CatalogConfigOptionController::class, 'index'])->name('catalog-config-options.index');
        Route::get('catalog-config-options/create', [CatalogConfigOptionController::class, 'create'])->name('catalog-config-options.create');
        Route::post('catalog-config-options', [CatalogConfigOptionController::class, 'store'])->name('catalog-config-options.store');
        Route::get('catalog-config-options/{catalog_config_option}/edit', [CatalogConfigOptionController::class, 'edit'])->name('catalog-config-options.edit');
        Route::put('catalog-config-options/{catalog_config_option}', [CatalogConfigOptionController::class, 'update'])->name('catalog-config-options.update');
        Route::post('catalog-config-options/{catalog_config_option}/status', [CatalogConfigOptionController::class, 'updateStatus'])->name('catalog-config-options.status');

        Route::get('product-colours', [ProductColourController::class, 'index'])->name('product-colours.index');
        Route::get('product-colours/create', [ProductColourController::class, 'create'])->name('product-colours.create');
        Route::post('product-colours', [ProductColourController::class, 'store'])->name('product-colours.store');
        Route::get('product-colours/{product_colour}/edit', [ProductColourController::class, 'edit'])->name('product-colours.edit');
        Route::put('product-colours/{product_colour}', [ProductColourController::class, 'update'])->name('product-colours.update');
        Route::post('product-colours/{product_colour}/status', [ProductColourController::class, 'updateStatus'])->name('product-colours.status');

        Route::get('product-finishes', [ProductFinishController::class, 'index'])->name('product-finishes.index');
        Route::get('product-finishes/create', [ProductFinishController::class, 'create'])->name('product-finishes.create');
        Route::post('product-finishes', [ProductFinishController::class, 'store'])->name('product-finishes.store');
        Route::get('product-finishes/{product_finish}/edit', [ProductFinishController::class, 'edit'])->name('product-finishes.edit');
        Route::put('product-finishes/{product_finish}', [ProductFinishController::class, 'update'])->name('product-finishes.update');
        Route::post('product-finishes/{product_finish}/status', [ProductFinishController::class, 'updateStatus'])->name('product-finishes.status');

        Route::get('product-upholsteries', [ProductUpholsteryController::class, 'index'])->name('product-upholsteries.index');
        Route::get('product-upholsteries/create', [ProductUpholsteryController::class, 'create'])->name('product-upholsteries.create');
        Route::post('product-upholsteries', [ProductUpholsteryController::class, 'store'])->name('product-upholsteries.store');
        Route::get('product-upholsteries/{product_upholstery}/edit', [ProductUpholsteryController::class, 'edit'])->name('product-upholsteries.edit');
        Route::put('product-upholsteries/{product_upholstery}', [ProductUpholsteryController::class, 'update'])->name('product-upholsteries.update');
        Route::post('product-upholsteries/{product_upholstery}/status', [ProductUpholsteryController::class, 'updateStatus'])->name('product-upholsteries.status');

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
        Route::match(['post', 'put'], 'transfers/{transfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('transfers.dispatch');
        Route::post('transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->name('transfers.cancel');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('orders/{order}/dispatch', [OrderController::class, 'dispatchOrder'])->name('orders.dispatch');
        Route::post('orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
    });
});
