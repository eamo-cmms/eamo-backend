<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\Checklist\Actions\ChecklistDetail\DeleteChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\ChecklistDetail\IndexChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\ChecklistDetail\StoreChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\ChecklistDetail\UpdateChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\ChecklistSchedule\CompleteChecklistScheduleAction;
use Modules\Equipment\Checklist\Actions\ChecklistSchedule\DeleteDailyChecklistSchedulesAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\DeleteChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\GetEquipmentChecklistStatusAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\IndexChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\JudgeSessionAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\ShowChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\ShowDailySessionAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\StoreChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\ChecklistSession\UpdateChecklistSessionAction;

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function (): void {
    Route::get('checklist-sessions/equipment-status', GetEquipmentChecklistStatusAction::class);
    Route::get('checklist-sessions/daily', ShowDailySessionAction::class);

    // Checklist Sessions
    Route::get('checklist-sessions', IndexChecklistSessionAction::class);
    Route::get('checklist-sessions/{id}', ShowChecklistSessionAction::class);
    Route::post('checklist-sessions', StoreChecklistSessionAction::class);
    Route::put('checklist-sessions/{id}', UpdateChecklistSessionAction::class);
    Route::delete('checklist-sessions/{id}', DeleteChecklistSessionAction::class);
    Route::post('checklist-sessions/judge', JudgeSessionAction::class);

    // Checklist Details
    Route::get('checklist-details', IndexChecklistDetailAction::class);
    Route::post('checklist-details', StoreChecklistDetailAction::class);
    Route::put('checklist-details', UpdateChecklistDetailAction::class);
    Route::delete('checklist-details/{id}', DeleteChecklistDetailAction::class);

    // Checklist Schedules
    Route::post('checklist-schedules/{id}/complete', CompleteChecklistScheduleAction::class);
    Route::delete('checklist-schedules/daily', DeleteDailyChecklistSchedulesAction::class);
});
