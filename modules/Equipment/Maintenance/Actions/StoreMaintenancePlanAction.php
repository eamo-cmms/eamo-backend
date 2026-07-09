<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\StoreMaintenancePlanRequest;

final class StoreMaintenancePlanAction
{
    use AsAction;

    public function asController(StoreMaintenancePlanRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
