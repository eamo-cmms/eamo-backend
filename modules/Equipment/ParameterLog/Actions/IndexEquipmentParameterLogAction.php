<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

final class IndexEquipmentParameterLogAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $logs = EquipmentParameterLog::with(['equipment', 'parameter', 'unit'])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
