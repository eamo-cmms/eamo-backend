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
        $query = EquipmentParameterLog::with(['equipment', 'parameter', 'unit', 'user']);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $logs = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
