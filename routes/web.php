<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', env('FRONTEND_URL'));

Route::get('/dashboard', function (Illuminate\Http\Request $request) {
    $frontendUrl = rtrim((string) env('FRONTEND_URL'), '/');
    $targetInterface = $request->query('target_interface') ?? $request->input('target_interface');

    $intended = session()->get('url.intended');
    if ($intended && (str_contains($intended, 'state=%2Fportal') || str_contains($intended, 'state=/portal'))) {
        $targetInterface = 'OI';
    }

    if ($targetInterface === 'OI') {
        return redirect($frontendUrl . '/#/portal');
    }

    return redirect($frontendUrl . '/#/dashboard/workspace');
})->name('dashboard');

Route::get('/logout', LogoutController::class)->name('logout');

require __DIR__.'/auth.php';
