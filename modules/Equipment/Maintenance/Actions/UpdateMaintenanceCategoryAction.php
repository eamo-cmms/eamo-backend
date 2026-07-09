<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenanceCategoryRequest;
use Throwable;

final class UpdateMaintenanceCategoryAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateMaintenanceCategoryRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
