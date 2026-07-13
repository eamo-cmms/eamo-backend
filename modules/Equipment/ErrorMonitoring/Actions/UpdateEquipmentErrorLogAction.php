<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
use Modules\Equipment\ErrorMonitoring\Requests\UpdateEquipmentErrorLogRequest;
use Modules\Equipment\ErrorMonitoring\Services\UpdateEquipmentErrorLogService;
use Throwable;

final class UpdateEquipmentErrorLogAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(
        string $id,
        UpdateEquipmentErrorLogRequest $request,
        UpdateEquipmentErrorLogService $service
    ): JsonResponse {
        $log = EquipmentErrorLog::findOrFail($id);
        $updatedLog = $service->execute($log, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $updatedLog,
        ]);
    }
}
