<?php

use App\Http\Controllers\User\GetAuthenticatedUserController;
use Illuminate\Support\Facades\Route;

Route::get('/user', GetAuthenticatedUserController::class)
    ->middleware('auth:api');
