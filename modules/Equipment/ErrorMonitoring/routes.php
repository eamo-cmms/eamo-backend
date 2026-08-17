<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\ErrorMonitoring\Actions\DeleteEquipmentErrorLogAction;
use Modules\Equipment\ErrorMonitoring\Actions\DeleteOperatingTimeAction;
use Modules\Equipment\ErrorMonitoring\Actions\GetMaintenanceStatusChartAction;
use Modules\Equipment\ErrorMonitoring\Actions\ImportOperatingTimeAction;
use Modules\Equipment\ErrorMonitoring\Actions\IndexEquipmentErrorLogAction;
use Modules\Equipment\ErrorMonitoring\Actions\IndexOperatingTimeAction;
use Modules\Equipment\ErrorMonitoring\Actions\StoreEquipmentErrorLogAction;
use Modules\Equipment\ErrorMonitoring\Actions\StoreOperatingTimeAction;
use Modules\Equipment\ErrorMonitoring\Actions\UpdateEquipmentErrorLogAction;
use Modules\Equipment\ErrorMonitoring\Actions\UpdateOperatingTimeAction;

Route::group([], function (): void {
    Route::prefix('v1/equipment/error-monitoring/equipment-error-logs')->name('equipment-error-logs.')->group(function (): void {
        Route::get('/', IndexEquipmentErrorLogAction::class)->name('index');

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentErrorLogAction::class)->name('store');
            Route::put('/{id}', UpdateEquipmentErrorLogAction::class)->name('update');
            Route::delete('/{id}', DeleteEquipmentErrorLogAction::class)->name('destroy');
        });
    });

    Route::prefix('v1/equipment/error-monitoring/operating-times')->name('operating-times.')->group(function (): void {
        Route::get('/', IndexOperatingTimeAction::class)->name('index');
        Route::get('/maintenance-status', GetMaintenanceStatusChartAction::class)->name('maintenance-status');

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreOperatingTimeAction::class)->name('store');
            Route::post('/import', ImportOperatingTimeAction::class)->name('import');
            Route::put('/{id}', UpdateOperatingTimeAction::class)->name('update');
            Route::delete('/{id}', DeleteOperatingTimeAction::class)->name('destroy');
        });
    });
});
