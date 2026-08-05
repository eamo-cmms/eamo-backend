<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\ParameterLog\Actions\DeleteEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\GetWeeklyEquipmentParameterLogsAction;
use Modules\Equipment\ParameterLog\Actions\ImportEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\IndexEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\OverviewEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\SaveEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\ShowEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\StoreEquipmentParameterLogAction;
use Modules\Equipment\ParameterLog\Actions\UpdateEquipmentParameterLogAction;

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function (): void {
    Route::prefix('equipment/equipment-parameter/logs')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/', IndexEquipmentParameterLogAction::class)->name('equipment-parameter-logs.index');
            Route::get('/weekly/{equipmentId}', GetWeeklyEquipmentParameterLogsAction::class)->name('equipment-parameter-logs.weekly');
            Route::get('/overview/{id}', OverviewEquipmentParameterLogAction::class)->name('equipment-parameter-logs.overview');
            Route::get('/{id}', ShowEquipmentParameterLogAction::class)->name('equipment-parameter-logs.show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentParameterLogAction::class)->name('equipment-parameter-logs.store');
            Route::post('/import', ImportEquipmentParameterLogAction::class)->name('equipment-parameter-logs.import');
            Route::put('/{id}', UpdateEquipmentParameterLogAction::class)->name('equipment-parameter-logs.update');
            Route::delete('/{id}', DeleteEquipmentParameterLogAction::class)->name('equipment-parameter-logs.delete');
            Route::post('/save', SaveEquipmentParameterLogAction::class)->name('equipment-parameter-logs.save');
        });
    });
});
