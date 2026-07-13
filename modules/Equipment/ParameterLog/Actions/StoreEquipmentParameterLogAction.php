<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
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
        $log = EquipmentParameterLog::create($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $log->load(['equipment', 'parameter', 'unit']),
        ], 201);
    }
}
