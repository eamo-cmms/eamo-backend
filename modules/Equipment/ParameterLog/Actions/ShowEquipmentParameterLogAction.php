<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

final class ShowEquipmentParameterLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = EquipmentParameterLog::with(['equipment', 'parameter', 'unit', 'user'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $log,
        ]);
    }
}
