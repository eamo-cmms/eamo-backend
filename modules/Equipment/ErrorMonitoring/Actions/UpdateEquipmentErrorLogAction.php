<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Requests\UpdateEquipmentErrorLogRequest;
use Throwable;

final class UpdateEquipmentErrorLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateEquipmentErrorLogRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
