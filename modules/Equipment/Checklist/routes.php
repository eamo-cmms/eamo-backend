<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Equipment\Checklist\Actions\DeleteChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\DeleteChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\IndexChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\IndexChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\JudgeSessionAction;
use Modules\Equipment\Checklist\Actions\ShowChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\StoreChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\StoreChecklistSessionAction;
use Modules\Equipment\Checklist\Actions\UpdateChecklistDetailAction;
use Modules\Equipment\Checklist\Actions\UpdateChecklistSessionAction;

Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function () {
    // Checklist Sessions
    Route::get('checklist-sessions', IndexChecklistSessionAction::class);
    Route::post('checklist-sessions', StoreChecklistSessionAction::class);
    Route::get('checklist-sessions/{id}', ShowChecklistSessionAction::class);
    Route::put('checklist-sessions/{id}', UpdateChecklistSessionAction::class);
    Route::delete('checklist-sessions/{id}', DeleteChecklistSessionAction::class);
    Route::post('checklist-sessions/judge', JudgeSessionAction::class);

    // Checklist Details
    Route::get('checklist-details', IndexChecklistDetailAction::class);
    Route::post('checklist-details', StoreChecklistDetailAction::class);
    Route::put('checklist-details', UpdateChecklistDetailAction::class);
    Route::delete('checklist-details/{id}', DeleteChecklistDetailAction::class);
});
