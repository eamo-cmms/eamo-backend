<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

final class ShowMaintenancePlanAction
{
    use AsAction;

    public function asController(string $id, Request $request): JsonResponse
    {
        $query = MaintenancePlan::with([
            'equipment',
            'maintenanceSchedule.maintenanceItem.maintenanceCategory',
            'maintenanceSchedule.users',
            'maintenanceSchedule.maintenanceLogs',
        ]);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $plan = $query->findOrFail($id);

        return response()->json($plan);
    }
}
