<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

final class CompleteMaintenanceScheduleAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $schedule = MaintenanceSchedule::findOrFail($id);

        $log = $schedule->maintenanceLogs()->first();
        if ($log) {
            $log->update([
                'result' => 'Completed',
                'log_date' => Carbon::today(),
                'note' => $log->note ?: 'Quick completed from dashboard',
            ]);
        } else {
            $log = $schedule->maintenanceLogs()->create([
                'result' => 'Completed',
                'log_date' => Carbon::today(),
                'note' => 'Quick completed from dashboard',
            ]);
        }

        return response()->json([
            'message' => 'Maintenance schedule marked completed successfully.',
            'schedule_id' => $schedule->id,
            'is_completed' => true,
        ]);
    }
}
