<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Requests\UpdateEquipmentParameterLogRequest;
use Throwable;

final class UpdateEquipmentParameterLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateEquipmentParameterLogRequest $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
