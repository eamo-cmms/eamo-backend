<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class IndexChecklistSessionAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = ChecklistSession::query()
            ->with(['equipment', 'details.schedules.logs', 'users']);

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        // Filter by date (via associated schedules)
        if ($request->has('session_date')) {
            $query->whereHas('schedules', function ($q) use ($request) {
                $q->whereDate('date', $request->input('session_date'));
            });
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereHas('schedules', function ($q) use ($request) {
                $q->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
            });
        }

        // Filter by equipment_id
        if ($request->has('equipment_id')) {
            $query->where('equipment_id', $request->input('equipment_id'));
        }

        // Filter by name or q
        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->input('name').'%');
        }
        if ($request->has('q')) {
            $qVal = $request->input('q');
            $query->where(function ($q) use ($qVal) {
                $q->where('name', 'like', '%'.$qVal.'%')
                    ->orWhere('equipment_id', 'like', '%'.$qVal.'%');
            });
        }

        $sessions = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        // Format the output to match ChecklistSession response structure
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
