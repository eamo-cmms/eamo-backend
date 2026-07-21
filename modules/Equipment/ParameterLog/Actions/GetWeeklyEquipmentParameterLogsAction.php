<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

final class GetWeeklyEquipmentParameterLogsAction
{
    use AsAction;

    public function asController(Request $request, string $equipmentId): JsonResponse
    {
        $oneWeekAgo = CarbonImmutable::now()->subDays(7)->startOfDay();

        $logs = EquipmentParameterLog::with(['equipment', 'parameter', 'unit', 'user'])
            ->where('equipment_id', $equipmentId)
            ->where(function ($query) use ($oneWeekAgo): void {
                $query->where('recorded_at', '>=', $oneWeekAgo)
                    ->orWhere(function ($q) use ($oneWeekAgo): void {
                        $q->whereNull('recorded_at')
                            ->where('created_at', '>=', $oneWeekAgo);
                    });
            })
            ->latest('recorded_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }
}
