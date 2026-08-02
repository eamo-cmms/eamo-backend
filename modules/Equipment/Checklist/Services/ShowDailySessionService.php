<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Carbon\Carbon;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Queries\ChecklistScheduleQuery;

final class ShowDailySessionService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{status: int, data: array<string, mixed>}
     */
    public function execute(array $data): array
    {
        $equipmentId = $data['equipment_id'];
        $dateString = $data['date'] ?? Carbon::today()->toDateString();
        $date = Carbon::parse($dateString);

        $query = ChecklistScheduleQuery::make()
            ->withDetail()
            ->withLogs()
            ->withUsers()
            ->forEquipment($equipmentId)
            ->forDate($date->toDateString());

        if (! empty($data['only_trashed'])) {
            $query->includeTrashed(only: true);
        } elseif (! empty($data['with_trashed'])) {
            $query->includeTrashed();
        }

        $schedules = $query->get();

        if ($schedules->isEmpty()) {
            return [
                'status' => 200,
                'data' => [
                    'message' => 'Checklist session not found for this date.',
                ],
            ];
        }

        $firstSessionId = $schedules->first()?->checklist_session_id;
        $session = null;
        if ($firstSessionId) {
            $sessionQuery = ChecklistSession::where('id', $firstSessionId);
            if (! empty($data['only_trashed'])) {
                $sessionQuery->onlyTrashed();
            } elseif (! empty($data['with_trashed'])) {
                $sessionQuery->withTrashed();
            }
            $session = $sessionQuery->first();
        }

        if (! $session) {
            $sessionQuery = ChecklistSession::where('equipment_id', $equipmentId);
            if (! empty($data['only_trashed'])) {
                $sessionQuery->onlyTrashed();
            } elseif (! empty($data['with_trashed'])) {
                $sessionQuery->withTrashed();
            }
            $session = $sessionQuery->first();
        }

        $detailsData = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->checklist_detail_id,
                'schedule_id' => $schedule->id,
                'checklist_id' => $schedule->checklistDetail?->checklist_id,
                'description' => $schedule->checklistDetail?->description,
                'deleted_at' => $schedule->deleted_at,
                'checklist_detail_deleted_at' => $schedule->checklistDetail?->deleted_at,
                'logs' => $schedule->logs,
                'users' => $schedule->users,
            ];
        });

        return [
            'status' => 200,
            'data' => [
                'id' => $session?->id,
                'name' => $session?->name ?? "Checklist - {$equipmentId}",
                'equipment_id' => $equipmentId,
                'schedule_mode' => $session?->schedule_mode ?? 'repeating',
                'cycle_type' => $session?->cycle_type,
                'cycle_interval' => $session?->cycle_interval,
                'session_date' => $date->toDateString(),
                'deleted_at' => $session?->deleted_at,
                'details' => $detailsData,
            ],
        ];
    }
}
