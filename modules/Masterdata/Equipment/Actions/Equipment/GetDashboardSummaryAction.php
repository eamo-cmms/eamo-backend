<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Masterdata\Equipment\Models\Equipment;

final class GetDashboardSummaryAction
{
    use AsAction;

    /**
     * Get statistics summary for dashboard widgets.
     */
    public function asController(Request $request): JsonResponse
    {
        // 1. Total Assets
        $totalAssets = Equipment::count();

        // 2. Active / Inactive
        $activeCount = Equipment::where('is_active', true)->count();
        $inactiveCount = Equipment::where('is_active', false)->count();

        // 3. Equipment with Errors
        $withErrors = Equipment::has('equipmentErrors')->count();

        // 4. Upcoming / Overdue Maintenance
        $equipments = Equipment::where('is_active', true)
            ->whereNotNull('maintenance_interval_hours')
            ->where('maintenance_interval_hours', '>', 0)
            ->get();

        $overdueCount = 0;
        $upcomingCount = 0;

        foreach ($equipments as $equipment) {
            $limit = $equipment->maintenance_interval_hours;
            $lastMaintenance = $equipment->last_maintenance;
            $lastMaintenanceDate = $lastMaintenance['datetime'] ?? null;

            $actualOp = OperatingTime::query()
                ->where('equipment_id', $equipment->id)
                ->when($lastMaintenanceDate, function ($query) use ($lastMaintenanceDate) {
                    $query->where('start_time', '>=', $lastMaintenanceDate);
                })
                ->sum('actual_operating_time');

            $remaining = $limit - $actualOp;

            if ($remaining <= 0) {
                $overdueCount++;
            } elseif ($remaining <= ($limit * 0.1)) {
                $upcomingCount++;
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_assets' => [
                    'title' => 'total_assets',
                    'value' => $totalAssets,
                    'description' => 'Total number of assets in the system',
                    'icon' => 'DatabaseOutlined',
                ],
                'active_inactive' => [
                    'title' => 'active_inactive',
                    'value' => "{$activeCount} / {$inactiveCount}",
                    'active' => $activeCount,
                    'inactive' => $inactiveCount,
                    'description' => 'Ratio of active vs inactive equipment',
                    'icon' => 'CheckCircleOutlined',
                ],
                'with_errors' => [
                    'title' => 'with_errors',
                    'value' => $withErrors,
                    'description' => 'Number of equipments currently recording errors',
                    'icon' => 'WarningOutlined',
                ],
                'maintenance' => [
                    'title' => 'maintenance',
                    'value' => $overdueCount,
                    'overdue' => $overdueCount,
                    'upcoming' => $upcomingCount,
                    'description' => 'Number of equipments exceeding their maintenance cycle or approaching maintenance limit',
                    'icon' => 'ClockCircleOutlined',
                ],
            ],
        ]);
    }
}
