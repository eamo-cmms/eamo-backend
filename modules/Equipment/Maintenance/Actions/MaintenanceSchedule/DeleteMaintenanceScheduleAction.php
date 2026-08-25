<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceSchedule;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

final class DeleteMaintenanceScheduleAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        Gate::authorize('delete', $schedule);

        $schedule->delete();

        return response()->json([
            'message' => 'Maintenance schedule deleted successfully.',
        ]);
    }
}
