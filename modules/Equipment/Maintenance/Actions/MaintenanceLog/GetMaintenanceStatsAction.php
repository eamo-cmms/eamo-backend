<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Masterdata\Equipment\Models\Equipment;

final class GetMaintenanceStatsAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        // 1. Top Maintained Equipment (Top 7)
        $topMaintained = MaintenanceLog::query()
            ->whereNotNull('equipment_id')
            ->select('equipment_id', DB::raw('COUNT(*) as total_count'))
            ->groupBy('equipment_id')
            ->orderByDesc('total_count')
            ->limit(7)
            ->with('equipment:id,code,name')
            ->get()
            ->map(function ($item) {
                $code = $item->equipment?->code ?? '—';
                $name = $item->equipment?->name;
                $label = $name ? "{$code} ({$name})" : $code;

                return [
                    'equipment_id' => $item->equipment_id,
                    'name'         => $label,
                    'code'         => $code,
                    'count'        => (int) $item->total_count,
                ];
            });

        // 2. Maintenance Warning Status (Healthy, Upcoming, Overdue)
        $equipments = Equipment::where('is_active', true)
            ->whereNotNull('maintenance_interval_hours')
            ->where('maintenance_interval_hours', '>', 0)
            ->select(['id', 'maintenance_interval_hours', 'last_maintenance'])
            ->get();

        $healthyCount = 0;
        $upcomingCount = 0;
        $overdueCount = 0;

        if ($equipments->isNotEmpty()) {
            foreach ($equipments as $eq) {
                $interval = (float) $eq->maintenance_interval_hours;
                $cutoff = ! empty($eq->last_maintenance['datetime'])
                    ? Carbon::parse($eq->last_maintenance['datetime'])
                    : null;

                $opQuery = DB::table('eamo_operating_times')
                    ->where('equipment_id', $eq->id)
                    ->whereNull('deleted_at');

                if ($cutoff) {
                    $opQuery->where('start_time', '>=', $cutoff);
                }

                $accumulated = (float) $opQuery->sum('actual_operating_time');
                $remaining = $interval - $accumulated;

                if ($remaining <= 0) {
                    $overdueCount++;
                } elseif ($remaining <= $interval * 0.2) { // remaining <= 20%
                    $upcomingCount++;
                } else {
                    $healthyCount++;
                }
            }
        }

        // Also consider active equipments without interval as healthy by default
        $noIntervalCount = Equipment::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('maintenance_interval_hours')
                  ->orWhere('maintenance_interval_hours', '<=', 0);
            })
            ->count();

        $healthyCount += $noIntervalCount;

        $warningStatus = [
            [
                'key'   => 'healthy',
                'name'  => 'Bình thường',
                'value' => $healthyCount,
                'color' => '#10b981', // green
            ],
            [
                'key'   => 'upcoming',
                'name'  => 'Sắp đến hạn',
                'value' => $upcomingCount,
                'color' => '#f59e0b', // amber
            ],
            [
                'key'   => 'overdue',
                'name'  => 'Quá hạn bảo trì',
                'value' => $overdueCount,
                'color' => '#ef4444', // red
            ],
        ];

        // 3. Maintenance Type Distribution (Load thẳng nội dung raw từ database)
        $typeDistribution = MaintenanceLog::query()
            ->select(DB::raw("COALESCE(type, 'Unspecified') as log_type"), DB::raw('COUNT(*) as total_count'))
            ->groupBy('log_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type'  => (string) $item->log_type,
                    'name'  => (string) $item->log_type,
                    'value' => (int) $item->total_count,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'top_maintained'    => $topMaintained,
                'warning_status'    => $warningStatus,
                'type_distribution' => $typeDistribution,
            ],
        ]);
    }
}
