<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenanceScheduleRequest;
use Throwable;

final class UpdateMaintenanceScheduleAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateMaintenanceScheduleRequest $request): JsonResponse
    {
        $schedule = MaintenanceSchedule::findOrFail($id);

        $validated = $request->validated();

        $scheduleFields = array_intersect_key($validated, array_flip([
            'actual_start_time', 'actual_end_time', 'result', 'note',
        ]));

        if (! empty($scheduleFields)) {
            $schedule->update($scheduleFields);
        }

        // Sync users if provided
        if (array_key_exists('user_ids', $validated)) {
            $schedule->users()->sync($validated['user_ids'] ?? []);
        }

        return response()->json(
            $schedule->fresh()->load(['maintenanceItem', 'users'])
        );
    }
}
