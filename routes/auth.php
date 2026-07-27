<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::middleware('guest')->group(function () {
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});
