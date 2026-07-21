<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
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
        $log = EquipmentParameterLog::findOrFail($id);
        $log->update($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $log->load(['equipment', 'parameter', 'unit', 'user']),
        ]);
    }
}
