<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Requests\StoreEquipmentParameterLogRequest;
use Throwable;

final class StoreEquipmentParameterLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(StoreEquipmentParameterLogRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
