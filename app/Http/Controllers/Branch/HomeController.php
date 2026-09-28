<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $branch = $request->user()->employee->branch;
        $sales = Order::query()
            ->where('branch_id', $branch->id)
            ->where('channel', Order::CHANNEL_BRANCH)
            ->whereDate('created_at', today());

        return view('branch.home', [
            'branch' => $branch,
            'saleCount' => (clone $sales)->count(),
            'saleTotal' => (clone $sales)->sum('total'),
        ]);
    }
}
