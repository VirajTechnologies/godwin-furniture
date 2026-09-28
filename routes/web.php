<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
