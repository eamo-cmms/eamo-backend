<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenancePlanRequest;
use Throwable;

final class UpdateMaintenancePlanAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateMaintenancePlanRequest $fields): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
