<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('http://localhost:5173');
});

Route::get('/dashboard', function () {
    return redirect('http://localhost:5173');
})->name('dashboard');

Route::get('/logout', function (\Illuminate\Http\Request $request) {
    auth('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('http://localhost:5173');
})->name('logout');

require __DIR__.'/auth.php';
