<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Requests\SaveEquipmentErrorLogRequest;
use Throwable;

final class SaveEquipmentErrorLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(SaveEquipmentErrorLogRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
