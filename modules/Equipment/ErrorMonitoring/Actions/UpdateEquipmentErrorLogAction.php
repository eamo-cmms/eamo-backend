<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
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
        $log = EquipmentErrorLog::findOrFail($id);
        $log->update($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $log->load(['equipment', 'equipmentError', 'handler']),
        ]);
    }
}
