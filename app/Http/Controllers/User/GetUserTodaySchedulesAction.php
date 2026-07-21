<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Queries\ChecklistScheduleQuery;
use Modules\Equipment\Maintenance\Queries\MaintenanceScheduleQuery;

final class GetUserTodaySchedulesAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $today = Carbon::today()->toDateString();

        // 1. Query checklist schedules using ChecklistScheduleQuery builder
        $checklistSchedules = ChecklistScheduleQuery::make()
            ->forDate($today)
            ->assignedTo($userId)
            ->withLogs()
            ->withEquipment()
            ->withDetail()
            ->get();

        $checklistSchedules->load('checklistSession');

        $checklistSchedules = $checklistSchedules->map(function ($schedule) {
            $latestLog = $schedule->logs->where('status', 'completed')->sortBy('checked_at')->last();

            return [
                'id' => $schedule->id,
                'checklist_session_id' => $schedule->checklist_session_id,
                'session_name' => $schedule->checklistSession?->name,
                'type' => 'checklist',
                'date' => $schedule->date,
                'equipment' => $schedule->equipment ? [
                    'id' => $schedule->equipment->id,
                    'name' => $schedule->equipment->name,
                    'code' => $schedule->equipment->code,
                ] : null,
                'detail' => $schedule->checklistDetail ? [
                    'id' => $schedule->checklistDetail->id,
                    'description' => $schedule->checklistDetail->description,
                ] : null,
                'logs' => $schedule->logs,
                'result' => $latestLog ? $latestLog->result : null,
                'is_completed' => $latestLog !== null,
            ];
        });

        // 2. Query maintenance schedules using MaintenanceScheduleQuery builder
        $maintenanceSchedules = MaintenanceScheduleQuery::make()
            ->dateRange($today, $today)
            ->assignedTo($userId)
            ->withLogs()
            ->withPlanFull()
            ->withItem()
            ->get()
            ->map(function ($schedule) {
                $latestLog = $schedule->maintenanceLogs->sortBy('log_date')->last();

                return [
                    'id' => $schedule->id,
                    'type' => 'maintenance',
                    'date' => $schedule->date,
                    'equipment' => $schedule->maintenancePlan?->equipment ? [
                        'id' => $schedule->maintenancePlan->equipment->id,
                        'name' => $schedule->maintenancePlan->equipment->name,
                        'code' => $schedule->maintenancePlan->equipment->code,
                    ] : null,
                    'item' => $schedule->maintenanceItem ? [
                        'id' => $schedule->maintenanceItem->id,
                        'name' => $schedule->maintenanceItem->name,
                    ] : null,
                    'logs' => $schedule->maintenanceLogs,
                    'result' => $latestLog ? $latestLog->result : null,
                    'is_completed' => $latestLog ? $latestLog->result === 'Completed' : false,
                ];
            });

        return response()->json([
            'date' => $today,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'checklist_schedules' => $checklistSchedules,
            'maintenance_schedules' => $maintenanceSchedules,
        ]);
    }
}
