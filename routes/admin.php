<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'super_admin'])->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

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
    });
});
