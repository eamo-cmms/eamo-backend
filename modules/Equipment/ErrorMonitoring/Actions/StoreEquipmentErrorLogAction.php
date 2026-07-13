<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
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
        $log = EquipmentErrorLog::create($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $log->load(['equipment', 'equipmentError', 'handler']),
        ], 201);
    }
}
