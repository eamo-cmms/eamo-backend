<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenancePlan;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteMaintenancePlanAction
{
    use AsAction;

    public function asController(string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $plan = MaintenancePlan::findOrFail($id);
        $cascadeService->deleteMaintenancePlan($plan);

        return response()->json(['message' => 'Maintenance plan deleted successfully.']);
    }
}
