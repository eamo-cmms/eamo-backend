<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteDailyChecklistSchedulesAction
{
    use AsAction;

    public function asController(Request $request, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'string', 'exists:eamo_checklist_sessions,id'],
            'equipment_id' => ['required', 'string', 'exists:eamo_equipment,id'],
            'date' => ['required', 'date'],
        ]);

        $schedules = ChecklistSchedule::query()
            ->where('checklist_session_id', $data['session_id'])
            ->where('equipment_id', $data['equipment_id'])
            ->whereDate('date', Carbon::parse($data['date'])->toDateString())
            ->get();

        $cascadeService->deleteChecklistSchedules($schedules);

        return response()->json([
            'deleted_count' => $schedules->count(),
            'message' => 'Checklist schedules deleted successfully.',
        ]);
    }
}
