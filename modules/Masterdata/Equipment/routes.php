<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Masterdata\Equipment\Actions\Equipment\DecodeQrAndGetEquipmentAction;
use Modules\Masterdata\Equipment\Actions\Equipment\DeleteEquipmentAction;
use Modules\Masterdata\Equipment\Actions\Equipment\GetDashboardSummaryAction;
use Modules\Masterdata\Equipment\Actions\Equipment\IndexEquipmentAction;
use Modules\Masterdata\Equipment\Actions\Equipment\ShowEquipmentAction;
use Modules\Masterdata\Equipment\Actions\Equipment\StoreEquipmentAction;
use Modules\Masterdata\Equipment\Actions\Equipment\UpdateEquipmentAction;
use Modules\Masterdata\Equipment\Actions\Equipment\UpdateEquipmentErrorsAction;
use Modules\Masterdata\Equipment\Actions\Equipment\UpdateEquipmentParentAction;
use Modules\Masterdata\Equipment\Actions\EquipmentCategory\DeleteEquipmentCategoryAction;
use Modules\Masterdata\Equipment\Actions\EquipmentCategory\IndexEquipmentCategoryAction;
use Modules\Masterdata\Equipment\Actions\EquipmentCategory\ShowEquipmentCategoryAction;
use Modules\Masterdata\Equipment\Actions\EquipmentCategory\StoreEquipmentCategoryAction;
use Modules\Masterdata\Equipment\Actions\EquipmentCategory\UpdateEquipmentCategoryAction;
use Modules\Masterdata\Equipment\Actions\EquipmentError\DeleteEquipmentErrorAction;
use Modules\Masterdata\Equipment\Actions\EquipmentError\IndexEquipmentErrorAction;
use Modules\Masterdata\Equipment\Actions\EquipmentError\ShowEquipmentErrorAction;
use Modules\Masterdata\Equipment\Actions\EquipmentError\StoreEquipmentErrorAction;
use Modules\Masterdata\Equipment\Actions\EquipmentError\UpdateEquipmentErrorAction;
use Modules\Masterdata\Equipment\Actions\EquipmentParameter\DeleteEquipmentParameterAction;
use Modules\Masterdata\Equipment\Actions\EquipmentParameter\IndexEquipmentParameterAction;
use Modules\Masterdata\Equipment\Actions\EquipmentParameter\ShowEquipmentParameterAction;
use Modules\Masterdata\Equipment\Actions\EquipmentParameter\StoreEquipmentParameterAction;
use Modules\Masterdata\Equipment\Actions\EquipmentParameter\UpdateEquipmentParameterAction;
use Modules\Masterdata\Equipment\Actions\EquipmentState\DeleteEquipmentStateAction;
use Modules\Masterdata\Equipment\Actions\EquipmentState\IndexEquipmentStateAction;
use Modules\Masterdata\Equipment\Actions\EquipmentState\ShowEquipmentStateAction;
use Modules\Masterdata\Equipment\Actions\EquipmentState\StoreEquipmentStateAction;
use Modules\Masterdata\Equipment\Actions\EquipmentState\UpdateEquipmentStateAction;
use Modules\Masterdata\Equipment\Actions\Unit\DeleteUnitAction;
use Modules\Masterdata\Equipment\Actions\Unit\IndexUnitAction;
use Modules\Masterdata\Equipment\Actions\Unit\ShowUnitAction;
use Modules\Masterdata\Equipment\Actions\Unit\StoreUnitAction;
use Modules\Masterdata\Equipment\Actions\Unit\UpdateUnitAction;

Route::group([], function (): void {
    Route::prefix('v1/equipment')->name('equipment.')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/dashboard/summary', GetDashboardSummaryAction::class)->name('dashboard.summary');
            Route::get('/', IndexEquipmentAction::class)->name('index');
            Route::post('/decode-qr', DecodeQrAndGetEquipmentAction::class)->name('decode-qr');
            Route::get('/{id}', ShowEquipmentAction::class)->name('show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentAction::class)->name('store');
            Route::put('/{id}', UpdateEquipmentAction::class)->name('update');
            Route::patch('/{id}/parent', UpdateEquipmentParentAction::class)->name('update-parent');
            Route::post('/{id}/errors', UpdateEquipmentErrorsAction::class)->name('update-errors');
            Route::delete('/{id}', DeleteEquipmentAction::class)->name('destroy');
        });
    });

    Route::prefix('v1/equipment-parameters')->name('equipment-parameters.')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/', IndexEquipmentParameterAction::class)->name('index');
            Route::get('/{id}', ShowEquipmentParameterAction::class)->name('show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentParameterAction::class)->name('store');
            Route::put('/{id}', UpdateEquipmentParameterAction::class)->name('update');
            Route::delete('/{id}', DeleteEquipmentParameterAction::class)->name('destroy');
        });
    });

    Route::prefix('v1/equipment-errors')->name('equipment-errors.')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/', IndexEquipmentErrorAction::class)->name('index');
            Route::get('/{id}', ShowEquipmentErrorAction::class)->name('show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentErrorAction::class)->name('store');
            Route::put('/{id}', UpdateEquipmentErrorAction::class)->name('update');
            Route::delete('/{id}', DeleteEquipmentErrorAction::class)->name('destroy');
        });
    });

    Route::prefix('v1/equipment-categories')->name('equipment-categories.')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/', IndexEquipmentCategoryAction::class)->name('index');
            Route::get('/{id}', ShowEquipmentCategoryAction::class)->name('show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentCategoryAction::class)->name('store');
            Route::put('/{id}', UpdateEquipmentCategoryAction::class)->name('update');
            Route::delete('/{id}', DeleteEquipmentCategoryAction::class)->name('destroy');
        });
    });

    Route::prefix('v1/units')->name('units.')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/', IndexUnitAction::class)->name('index');
            Route::get('/{id}', ShowUnitAction::class)->name('show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreUnitAction::class)->name('store');
            Route::put('/{id}', UpdateUnitAction::class)->name('update');
            Route::delete('/{id}', DeleteUnitAction::class)->name('destroy');
        });
    });

    Route::prefix('v1/equipment-states')->name('equipment-states.')->group(function (): void {
        Route::middleware('engineer')->group(function (): void {
            Route::get('/', IndexEquipmentStateAction::class)->name('index');
            Route::get('/{id}', ShowEquipmentStateAction::class)->name('show');
        });

        Route::middleware('manager')->group(function (): void {
            Route::post('/', StoreEquipmentStateAction::class)->name('store');
            Route::put('/{id}', UpdateEquipmentStateAction::class)->name('update');
            Route::delete('/{id}', DeleteEquipmentStateAction::class)->name('destroy');
        });
    });
});
