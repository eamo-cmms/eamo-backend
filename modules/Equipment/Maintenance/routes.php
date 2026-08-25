<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\Maintenance\Actions\MaintenanceCategory\DeleteMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceCategory\IndexMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceCategory\StoreMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceCategory\UpdateMaintenanceCategoryAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceItem\DeleteMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceItem\IndexMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceItem\StoreMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceItem\UpdateMaintenanceItemAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceLog\DeleteMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceLog\IndexMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceLog\StoreMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceLog\UpdateMaintenanceLogAction;
use Modules\Equipment\Maintenance\Actions\MaintenancePlan\DeleteMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\MaintenancePlan\IndexMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\MaintenancePlan\JudgeMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\MaintenancePlan\ShowMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\MaintenancePlan\StoreMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\MaintenancePlan\UpdateMaintenancePlanAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceSchedule\CompleteMaintenanceScheduleAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceSchedule\DeleteMaintenanceScheduleAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceSchedule\IndexMaintenanceScheduleAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceSchedule\UpdateMaintenanceScheduleAction;

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function (): void {
    // Maintenance Plans
    Route::get('maintenance-plans', IndexMaintenancePlanAction::class);
    Route::get('maintenance-plans/{id}', ShowMaintenancePlanAction::class);
    Route::post('maintenance-plans', StoreMaintenancePlanAction::class);
    Route::put('maintenance-plans/{id}', UpdateMaintenancePlanAction::class);
    Route::delete('maintenance-plans/{id}', DeleteMaintenancePlanAction::class);
    Route::post('maintenance-plans/judge', JudgeMaintenancePlanAction::class);

    // Maintenance Schedules
    Route::get('maintenance-schedules', IndexMaintenanceScheduleAction::class);
    Route::put('maintenance-schedules/{id}', UpdateMaintenanceScheduleAction::class);
    Route::delete('maintenance-schedules/{id}', DeleteMaintenanceScheduleAction::class);
    Route::post('maintenance-schedules/{id}/complete', CompleteMaintenanceScheduleAction::class);

    // Maintenance Logs
    Route::get('maintenance-logs', IndexMaintenanceLogAction::class);
    Route::post('maintenance-logs', StoreMaintenanceLogAction::class);
    Route::put('maintenance-logs/{id}', UpdateMaintenanceLogAction::class);
    Route::delete('maintenance-logs/{id}', DeleteMaintenanceLogAction::class);

    // Maintenance Categories
    Route::get('maintenance-categories', IndexMaintenanceCategoryAction::class);
    Route::post('maintenance-categories', StoreMaintenanceCategoryAction::class);
    Route::put('maintenance-categories/{id}', UpdateMaintenanceCategoryAction::class);
    Route::delete('maintenance-categories/{id}', DeleteMaintenanceCategoryAction::class);

    // Maintenance Items
    Route::get('maintenance-items', IndexMaintenanceItemAction::class);
    Route::post('maintenance-items', StoreMaintenanceItemAction::class);
    Route::put('maintenance-items/{id}', UpdateMaintenanceItemAction::class);
    Route::delete('maintenance-items/{id}', DeleteMaintenanceItemAction::class);
});
