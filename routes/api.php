<?php

use App\Http\Controllers\User\GetAuthenticatedUserController;
use App\Http\Controllers\User\LogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/user', GetAuthenticatedUserController::class)
    ->middleware('auth:api');

Route::post('/logout', LogoutController::class)
    ->middleware('auth:api');
