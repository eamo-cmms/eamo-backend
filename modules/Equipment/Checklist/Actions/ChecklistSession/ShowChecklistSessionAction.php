<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\ShowChecklistSessionRequest;

final class ShowChecklistSessionAction
{
    use AsAction;

    public function asController(string $id, ShowChecklistSessionRequest $request): JsonResponse
    {
        $options = $request->all();

        $relations = ['users', 'equipment'];
        if (! empty($options['with_details']) || ! empty($options['include_details'])) {
            $relations['details.schedules'] = function ($q) use ($options) {
                $q->when(! empty($options['date']), function ($sub) use ($options) {
                    $sub->whereDate('date', $options['date']);
                });
            };
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

        return response()->json([
            'id' => $session->id,
            'name' => $session->name,
            'equipment_id' => $session->equipment_id,
            'equipment' => $session->equipment ? [
                'id' => $session->equipment->id,
                'name' => $session->equipment->name,
                'code' => $session->equipment->code,
            ] : null,
            'session_date' => $session->session_date ? Carbon::parse($session->session_date)->toDateTimeString() : null,
            'deleted_at' => $session->deleted_at,
            'details' => $details,
            'schedules' => $schedules,
            'users' => $session->users ?? [],
        ]);
    }
}
