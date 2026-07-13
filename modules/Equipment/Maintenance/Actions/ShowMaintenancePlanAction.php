<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

final class ShowMaintenancePlanAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $plan = MaintenancePlan::with([
            'equipment',
            'maintenanceSchedule.maintenanceItem.maintenanceCategory',
            'maintenanceSchedule.users',
            'maintenanceSchedule.maintenanceLogs',
        ])->findOrFail($id);

        return response()->json($plan);
    }
}
