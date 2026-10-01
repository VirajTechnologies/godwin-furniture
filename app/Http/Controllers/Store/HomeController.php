<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('store.home');
    }

    public function catalog(): View
    {
        return view('store.catalog');
    }

    public function product(): View
    {
        return view('store.product');
    }

    public function cart(): View
    {
        return view('store.cart');
    }

    public function login(): View
    {
        return view('store.login');
    }

    public function register(): View
    {
        return view('store.register');
    }
}
