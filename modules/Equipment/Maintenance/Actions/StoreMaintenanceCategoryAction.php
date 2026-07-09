<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceCategoryRequest;
use Throwable;

final class StoreMaintenanceCategoryAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(StoreMaintenanceCategoryRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
