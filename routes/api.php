<?php

use App\Http\Controllers\Company\DestroyCompanyController;
use App\Http\Controllers\Company\IndexCompanyController;
use App\Http\Controllers\Company\ShowCompanyController;
use App\Http\Controllers\Company\StoreCompanyController;
use App\Http\Controllers\Company\UpdateCompanyController;
use App\Http\Controllers\Department\DestroyDepartmentController;
use App\Http\Controllers\Department\IndexDepartmentController;
use App\Http\Controllers\Department\ShowDepartmentController;
use App\Http\Controllers\Department\StoreDepartmentController;
use App\Http\Controllers\Department\UpdateDepartmentController;
use App\Http\Controllers\User\GetAuthenticatedUserController;
use App\Http\Controllers\User\LogoutController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    Route::get('/user', GetAuthenticatedUserController::class);
    Route::post('/logout', LogoutController::class);

    // Companies CRUD
    Route::get('/companies', IndexCompanyController::class);
    Route::post('/companies', StoreCompanyController::class);
    Route::get('/companies/{company}', ShowCompanyController::class);
    Route::put('/companies/{company}', UpdateCompanyController::class);
    Route::patch('/companies/{company}', UpdateCompanyController::class);
    Route::delete('/companies/{company}', DestroyCompanyController::class);

    // Departments CRUD
    Route::get('/departments', IndexDepartmentController::class);
    Route::post('/departments', StoreDepartmentController::class);
    Route::get('/departments/{department}', ShowDepartmentController::class);
    Route::put('/departments/{department}', UpdateDepartmentController::class);
    Route::patch('/departments/{department}', UpdateDepartmentController::class);
    Route::delete('/departments/{department}', DestroyDepartmentController::class);
});
