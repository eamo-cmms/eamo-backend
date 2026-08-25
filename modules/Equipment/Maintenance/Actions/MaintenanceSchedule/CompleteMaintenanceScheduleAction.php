<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceSchedule;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

final class CompleteMaintenanceScheduleAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        Gate::authorize('complete', $schedule);

        $log = $schedule->maintenanceLogs()->first();
        if ($log) {
            $log->update([
                'log_date' => Carbon::today(),
                'note' => $log->note ?: 'Quick completed from dashboard',
            ]);
        } else {
            $log = $schedule->maintenanceLogs()->create([
                'equipment_id' => $schedule->equipment_id,
                'log_date' => Carbon::today(),
                'note' => 'Quick completed from dashboard',
            ]);
        }

        return response()->json([
            'message' => __('maintenance.schedule_completed'),
            'schedule_id' => $schedule->id,
            'is_completed' => true,
        ]);
    }
}
