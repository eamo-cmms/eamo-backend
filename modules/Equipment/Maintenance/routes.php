<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\Maintenance\Actions\DeleteMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\DeleteMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\DeleteMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\DeleteMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\IndexMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\IndexMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\IndexMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\IndexMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\IndexMaintenanceScheduleAction;
use Modules\Equipment\Maintenance\Actions\ShowMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\StoreMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\StoreMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\StoreMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\StoreMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\UpdateMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\UpdateMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\UpdateMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\UpdateMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\UpdateMaintenanceScheduleAction;

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function (): void {
    Route::middleware('engineer')->group(function (): void {
        Route::get('maintenance-plans', IndexMaintenancePlanAction::class);
        Route::get('maintenance-plans/{id}', ShowMaintenancePlanAction::class);
        Route::get('maintenance-schedules', IndexMaintenanceScheduleAction::class);
        Route::get('maintenance-logs', IndexMaintenanceLogAction::class);
        Route::get('maintenance-categories', IndexMaintenanceCategoryAction::class);
        Route::get('maintenance-items', IndexMaintenanceItemAction::class);
    });

    Route::middleware('manager')->group(function (): void {
        Route::post('maintenance-plans', StoreMaintenancePlanAction::class);
        Route::put('maintenance-plans/{id}', UpdateMaintenancePlanAction::class);
        Route::delete('maintenance-plans/{id}', DeleteMaintenancePlanAction::class);

        Route::put('maintenance-schedules/{id}', UpdateMaintenanceScheduleAction::class);

        Route::post('maintenance-logs', StoreMaintenanceLogAction::class);
        Route::put('maintenance-logs/{id}', UpdateMaintenanceLogAction::class);
        Route::delete('maintenance-logs/{id}', DeleteMaintenanceLogAction::class);

        Route::post('maintenance-categories', StoreMaintenanceCategoryAction::class);
        Route::put('maintenance-categories/{id}', UpdateMaintenanceCategoryAction::class);
        Route::delete('maintenance-categories/{id}', DeleteMaintenanceCategoryAction::class);

        Route::post('maintenance-items', StoreMaintenanceItemAction::class);
        Route::put('maintenance-items/{id}', UpdateMaintenanceItemAction::class);
        Route::delete('maintenance-items/{id}', DeleteMaintenanceItemAction::class);
    });
});
