<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\IndexChecklistSessionRequest;

final class IndexChecklistSessionAction
{
    use AsAction;

    public function asController(IndexChecklistSessionRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $query = ChecklistSession::query()
            ->filter($filters)
            ->with(['equipment', 'details.schedules.logs', 'users']);

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

        return response()->json([
            'data' => $items,
            'current_page' => $sessions->currentPage(),
            'per_page' => $sessions->perPage(),
            'total' => $sessions->total(),
        ]);
    }
}
