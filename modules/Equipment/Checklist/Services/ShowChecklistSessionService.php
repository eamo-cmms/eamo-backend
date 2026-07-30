<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistSession;

final class ShowChecklistSessionService
{
    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function execute(string $id, array $options): array
    {
        $relations = ['users', 'equipment'];
        if (! empty($options['with_details']) || ! empty($options['include_details'])) {
            $relations[] = 'details.schedules.logs';
        }

        $query = ChecklistSession::query()->with($relations);

        if (! empty($options['only_trashed'])) {
            $query->onlyTrashed();
        } elseif (! empty($options['with_trashed'])) {
            $query->withTrashed();
        }

        $session = $query->findOrFail($id);

        $details = $session->details->map(function ($detail) {
            $logs = $detail->schedules ? $detail->schedules->flatMap->logs->values()->toArray() : [];

            return [
                'id' => $detail->id,
                'checklist_id' => $detail->checklist_id,
                'description' => $detail->description,
                'schedule_id' => $detail->schedules->first()?->id,
                'deleted_at' => $detail->deleted_at,
                'logs' => $logs,
            ];
        })->toArray();

        $schedules = $session->details->flatMap(function ($detail) {
            return $detail->schedules->map(function ($schedule) use ($detail) {
                return [
                    'id' => $schedule->id,
                    'date' => $schedule->date,
                    'checklist_detail_id' => $detail->id,
                    'checklist_id' => $detail->checklist_id,
                    'description' => $detail->description,
                    'deleted_at' => $schedule->deleted_at,
                    'logs' => $schedule->logs->values()->toArray(),
                ];
            });
        })->values()->toArray();

        return [
            'id' => $session->id,
            'name' => $session->name,
            'equipment_id' => $session->equipment_id,
            'equipment' => $session->equipment ? [
                'id' => $session->equipment->id,
                'name' => $session->equipment->name,
                'code' => $session->equipment->code,
            ] : null,
            'session_date' => $session->session_date ? \Carbon\Carbon::parse($session->session_date)->toDateTimeString() : null,
            'deleted_at' => $session->deleted_at,
            'details' => $details,
            'schedules' => $schedules,
            'users' => $session->users ?? [],
        ];
    }
}
