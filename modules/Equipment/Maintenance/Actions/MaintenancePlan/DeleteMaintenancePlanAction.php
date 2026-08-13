<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenancePlan;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

final class DeleteMaintenancePlanAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $plan = MaintenancePlan::findOrFail($id);
        $plan->delete();

        return response()->json(['message' => 'Maintenance plan deleted successfully.']);
    }
}
