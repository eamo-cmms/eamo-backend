<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class ShowDailySessionAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $equipmentId = $request->input('equipment_id');
        $dateString = $request->input('date') ?? Carbon::today()->toDateString();
        $date = Carbon::parse($dateString);

        // Find existing schedules for the equipment on this date
        $schedules = ChecklistSchedule::with(['checklistDetail', 'logs.users', 'users'])
            ->where('equipment_id', $equipmentId)
            ->whereDate('date', $date)
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json([
                'message' => 'Checklist session not found for this date.',
            ], 404);
        }

        $session = ChecklistSession::where('equipment_id', $equipmentId)->first();

        $detailsData = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->checklist_detail_id,
                'schedule_id' => $schedule->id,
                'checklist_id' => $schedule->checklistDetail?->checklist_id,
                'description' => $schedule->checklistDetail?->description,
                'logs' => $schedule->logs,
                'users' => $schedule->users,
            ];
        });

        return response()->json([
            'id' => $session?->id,
            'name' => $session?->name ?? "Checklist - {$equipmentId}",
            'equipment_id' => $equipmentId,
            'session_date' => $date->toDateString(),
            'details' => $detailsData,
        ]);
    }
}
