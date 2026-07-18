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
        $query = EquipmentErrorLog::with(['equipment', 'equipmentError', 'handlers']);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $logs = $query->latest('occurred_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
