<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Requests\StoreEquipmentErrorLogRequest;
use Modules\Equipment\ErrorMonitoring\Services\StoreEquipmentErrorLogService;
use Throwable;

final class StoreEquipmentErrorLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(
        StoreEquipmentErrorLogRequest $request,
        StoreEquipmentErrorLogService $service
    ): JsonResponse {
        $log = $service->execute($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $log,
        ], 201);
    }
}
