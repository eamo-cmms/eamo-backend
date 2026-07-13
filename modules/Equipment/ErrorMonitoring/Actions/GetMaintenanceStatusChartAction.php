<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Masterdata\Equipment\Models\Equipment;

final class GetMaintenanceStatusChartAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $equipments = Equipment::where('is_active', true)
            ->whereNotNull('maintenance_interval_hours')
            ->where('maintenance_interval_hours', '>', 0)
            ->get();

        $data = [];

        foreach ($equipments as $equipment) {
            $limit = $equipment->maintenance_interval_hours ?? 0;

            $lastMaintenance = $equipment->last_maintenance;
            $lastMaintenanceDate = isset($lastMaintenance['datetime']) ? $lastMaintenance['datetime'] : null;

            $actualOp = OperatingTime::where('equipment_id', $equipment->id)
                ->when($lastMaintenanceDate, function ($query) use ($lastMaintenanceDate) {
                    $query->where('start_time', '>=', $lastMaintenanceDate);
                })
                ->sum('actual_operating_time');

            $remaining = $limit - $actualOp;

            $data[] = [
                'name' => $equipment->code,
                'remaining' => round((float) $remaining, 2),
            ];
        }

        // Sort ascending by remaining so the lowest remaining hours is first
        usort($data, function ($a, $b) {
            return $a['remaining'] <=> $b['remaining'];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
