<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Requests\StoreEquipmentErrorLogRequest;
use Throwable;

final class StoreEquipmentErrorLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(StoreEquipmentErrorLogRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
