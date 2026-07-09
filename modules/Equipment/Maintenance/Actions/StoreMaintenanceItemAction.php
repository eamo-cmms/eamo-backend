<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceItemRequest;
use Throwable;

final class StoreMaintenanceItemAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(StoreMaintenanceItemRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
