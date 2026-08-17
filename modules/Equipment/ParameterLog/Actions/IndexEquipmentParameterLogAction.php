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

        if ($request->filled('equipment_id')) {
            $query->where('equipment_id', $request->query('equipment_id'));
        }

        if ($request->filled('equipment_parameter_id')) {
            $query->where('equipment_parameter_id', $request->query('equipment_parameter_id'));
        }

        if ($request->filled('start_date')) {
            $startDate = $request->query('start_date');
            $query->where(function ($q) use ($startDate) {
                $q->whereDate('recorded_at', '>=', $startDate)
                    ->orWhere(function ($q2) use ($startDate) {
                        $q2->whereNull('recorded_at')->whereDate('created_at', '>=', $startDate);
                    });
            });
        }

        if ($request->filled('end_date')) {
            $endDate = $request->query('end_date');
            $query->where(function ($q) use ($endDate) {
                $q->whereDate('recorded_at', '<=', $endDate)
                    ->orWhere(function ($q2) use ($endDate) {
                        $q2->whereNull('recorded_at')->whereDate('created_at', '<=', $endDate);
                    });
            });
        }

        $logs = $query->latest('recorded_at')->latest('created_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
