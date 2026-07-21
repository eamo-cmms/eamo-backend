<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use Modules\Equipment\Checklist\Models\ChecklistSession;

final class IndexChecklistSessionService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters): array
    {
        $query = ChecklistSession::query()
            ->with(['equipment', 'details.schedules.logs', 'users']);

        if (! empty($filters['only_trashed'])) {
            $query->onlyTrashed();
        } elseif (! empty($filters['with_trashed'])) {
            $query->withTrashed();
        }

        if (! empty($filters['session_date'])) {
            $query->whereHas('schedules', function ($q) use ($filters) {
                $q->whereDate('date', $filters['session_date']);
            });
        }
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $query->whereHas('schedules', function ($q) use ($filters) {
                $q->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
            });
        }

        if (! empty($filters['equipment_id'])) {
            $query->where('equipment_id', $filters['equipment_id']);
        }

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }
        if (! empty($filters['q'])) {
            $qVal = $filters['q'];
            $query->where(function ($q) use ($qVal) {
                $q->where('name', 'like', '%'.$qVal.'%')
                    ->orWhere('equipment_id', 'like', '%'.$qVal.'%');
            });
        }

        $perPage = (int) ($filters['per_page'] ?? 15);
        $sessions = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $items = collect($sessions->items())->map(function ($session) {
            $details = $session->details->map(function ($detail) {
                $logs = $detail->schedules->flatMap->logs->values()->toArray();

                return [
                    'id' => $detail->id,
                    'checklist_id' => $detail->checklist_id,
                    'description' => $detail->description,
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
                'session_date' => $session->session_date ? $session->session_date->toDateString() : null,
                'deleted_at' => $session->deleted_at,
                'details' => $details,
                'schedules' => $schedules,
                'users' => $session->users ?? [],
            ];
        })->values()->toArray();

        return [
            'data' => $items,
            'current_page' => $sessions->currentPage(),
            'per_page' => $sessions->perPage(),
            'total' => $sessions->total(),
        ];
    }
}
