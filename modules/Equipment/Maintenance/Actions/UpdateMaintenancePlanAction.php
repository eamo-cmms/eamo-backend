<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenancePlanRequest;
use Modules\Equipment\Maintenance\Services\MaintenancePlanUpdateService;
use Throwable;

final class UpdateMaintenancePlanAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(
        string $id,
        UpdateMaintenancePlanRequest $request,
        MaintenancePlanUpdateService $updateService
    ): JsonResponse {
        $plan = MaintenancePlan::with(['maintenanceSchedule'])->findOrFail($id);

        $updatedPlan = $updateService->update($plan, $request->validated());

        $updatedPlan->load([
            'equipment',
            'maintenanceSchedule.maintenanceItem.maintenanceCategory',
            'maintenanceSchedule.users',
            'maintenanceSchedule.maintenanceLogs',
        ]);

        return response()->json($updatedPlan);
    }
}
