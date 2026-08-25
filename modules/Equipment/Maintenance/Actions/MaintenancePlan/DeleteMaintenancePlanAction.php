<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenancePlan;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

final class DeleteMaintenancePlanAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $plan = MaintenancePlan::findOrFail($id);
        Gate::authorize('delete', $plan);

        $plan->delete();

        return response()->json(['message' => __('maintenance.plan_deleted')]);
    }
}
