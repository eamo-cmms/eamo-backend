<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'http://localhost:5173');

Route::redirect('/dashboard', 'http://localhost:5173')->name('dashboard');

Route::get('/logout', LogoutController::class)->name('logout');

require __DIR__.'/auth.php';
