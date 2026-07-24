<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\StoreMaintenancePlanRequest;
use Modules\Equipment\Maintenance\Services\StoreMaintenancePlanService;
use Throwable;

final class StoreMaintenancePlanAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(
        StoreMaintenancePlanRequest $request,
        StoreMaintenancePlanService $storeService
    ): JsonResponse {
        $plan = $storeService->execute($request->validated());

        return response()->json(
            $plan->load([
                'equipment',
                'maintenanceSchedule.maintenanceItem',
                'maintenanceSchedule.users',
            ]),
            201
        );
    }
}
