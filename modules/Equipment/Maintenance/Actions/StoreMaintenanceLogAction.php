<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class StoreMaintenanceLogAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'maintenance_schedule_id' => 'required|string|exists:eamo_maintenance_schedules,id',
            'result' => 'required|string|in:Completed,Partial,Failed',
            'note' => 'nullable|string',
            'type' => 'nullable|string',
        ]);

        $validated['log_date'] = now()->toDateString();

        $log = MaintenanceLog::create($validated);

        return response()->json($log, 201);
    }
}
