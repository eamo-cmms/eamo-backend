<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

final class IndexEquipmentErrorLogAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $logs = EquipmentErrorLog::with(['equipment', 'equipmentError', 'handler'])->latest('occurred_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
