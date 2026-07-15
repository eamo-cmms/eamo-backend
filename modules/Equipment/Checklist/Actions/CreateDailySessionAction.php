<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class CreateDailySessionAction
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

        // Verify schedules exist for this equipment on this date
        $exists = ChecklistSchedule::where('equipment_id', $equipmentId)
            ->whereDate('date', $date)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Checklist session already exists for this date.',
            ], 422);
        }

        // Require an existing session template for this equipment
        $session = ChecklistSession::where('equipment_id', $equipmentId)->first();
        if (! $session) {
            return response()->json([
                'message' => 'No checklist session template found for this equipment. Please create one first.',
            ], 404);
        }

        // Generate ChecklistSchedule records for this date from session details
        $details = $session->details;
        $currentUser = $request->user();
        $userIds = $currentUser ? [$currentUser->id] : [];

        foreach ($details as $detail) {
            $schedule = ChecklistSchedule::create([
                'id' => (string) Str::uuid(),
                'equipment_id' => $equipmentId,
                'checklist_session_id' => $session->id,
                'checklist_detail_id' => $detail->id,
                'date' => $date->toDateString(),
                'original_date' => $date->toDateString(),
                'is_rescheduled' => false,
            ]);

            $schedule->logs()->create([
                'status' => 'pending',
                'result' => null,
            ]);

            if (! empty($userIds)) {
                $schedule->users()->sync($userIds);
            }
        }

        // Fetch new schedules and return
        $schedules = ChecklistSchedule::with(['checklistDetail', 'logs', 'users'])
            ->where('checklist_session_id', $session->id)
            ->whereDate('date', $date)
            ->get();

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
            'id' => $session->id,
            'name' => $session->name,
            'equipment_id' => $equipmentId,
            'session_date' => $date->toDateString(),
            'details' => $detailsData,
        ], 201);
    }
}
