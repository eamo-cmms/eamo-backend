<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class CreateDailySessionService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{status: int, data: array<string, mixed>}
     */
    public function execute(array $data, ?User $currentUser): array
    {
        $equipmentId = $data['equipment_id'];
        $dateString = $data['date'] ?? Carbon::today()->toDateString();
        $date = Carbon::parse($dateString);

        $exists = ChecklistSchedule::where('equipment_id', $equipmentId)
            ->whereDate('date', $date)
            ->exists();

        if ($exists) {
            return [
                'status' => 422,
                'data' => [
                    'message' => 'Checklist session already exists for this date.',
                ],
            ];
        }

        $session = ChecklistSession::where('equipment_id', $equipmentId)->first();
        if (! $session) {
            return [
                'status' => 404,
                'data' => [
                    'message' => 'No checklist session template found for this equipment. Please create one first.',
                ],
            ];
        }

        $details = $session->details;
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

        return [
            'status' => 201,
            'data' => [
                'id' => $session->id,
                'name' => $session->name,
                'equipment_id' => $equipmentId,
                'session_date' => $date->toDateString(),
                'details' => $detailsData,
            ],
        ];
    }
}
