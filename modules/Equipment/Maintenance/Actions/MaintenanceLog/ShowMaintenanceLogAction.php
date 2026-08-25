<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class ShowMaintenanceLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = MaintenanceLog::with([
            'equipment.equipmentCategory',
            'user:id,name,email',
            'maintenanceSchedule.maintenancePlan',
        ])->findOrFail($id);

        return response()->json($log);
    }
}
