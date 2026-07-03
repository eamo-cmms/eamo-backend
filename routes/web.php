<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('http://localhost:5173');
});

Route::get('/dashboard', function () {
    return redirect('http://localhost:5173');
})->name('dashboard');

require __DIR__.'/auth.php';
