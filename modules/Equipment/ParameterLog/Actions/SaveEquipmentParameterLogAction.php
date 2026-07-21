<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
use Modules\Equipment\ParameterLog\Requests\SaveEquipmentParameterLogRequest;

final class SaveEquipmentParameterLogAction
{
    use AsAction;

    public function asController(SaveEquipmentParameterLogRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $equipmentId = $validated['equipment_id'];
        $userId = $validated['user_id'] ?? $request->user()?->id;
        $recordedAt = $validated['recorded_at'] ?? now();

        $savedLogs = [];
        $parameters = $validated['parameters'] ?? [];

        foreach ($parameters as $param) {
            if (isset($param['value']) && $param['value'] !== null && $param['value'] !== '') {
                $savedLogs[] = EquipmentParameterLog::create([
                    'equipment_id' => $equipmentId,
                    'equipment_parameter_id' => $param['equipment_parameter_id'],
                    'unit_id' => $param['unit_id'] ?? null,
                    'value' => (string) $param['value'],
                    'user_id' => $userId,
                    'recorded_at' => $param['recorded_at'] ?? $recordedAt,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Equipment parameter logs recorded successfully',
            'data' => $savedLogs,
        ]);
    }
}
