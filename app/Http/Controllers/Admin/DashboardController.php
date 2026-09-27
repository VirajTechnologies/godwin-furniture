<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'warehouseCount' => Warehouse::query()->count(),
            'activeCount' => Warehouse::query()->where('status', Warehouse::STATUS_ACTIVE)->count(),
            'primaryWarehouse' => Warehouse::query()->with('district')->where('is_primary', true)->first(),
        ]);
    }
}
