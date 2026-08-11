<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class GetDashboardSummaryAction
{
    use AsAction;

    /**
     * Get statistics summary for dashboard widgets.
     */
    public function asController(Request $request): JsonResponse
    {
        // 1. Total Assets (SoftDeletes global scope excludes deleted records automatically)
        $totalAssets = Equipment::count();

        // 2. Active / Inactive
        $activeCount   = Equipment::where('is_active', true)->count();
        $inactiveCount = Equipment::where('is_active', false)->count();

        // 3. Equipment with active (unhandled) error logs
        // Uses EquipmentErrorLog directly to count equipment that have open error cases
        // (occurred_at set, handled_at IS NULL, not soft-deleted)
        $withErrors = Equipment::whereHas('errorLogs', function ($q): void {
            $q->whereNull('handled_at');
        })->count();

        // 4. Upcoming / Overdue Maintenance — resolved in a single aggregated DB query (no N+1)
        //
        // Logic:
        //   - Only active equipments with a positive maintenance_interval_hours are candidates.
        //   - Accumulated actual_operating_time is summed from eamo_operating_times
        //     starting AFTER the equipment's last_maintenance.datetime (if recorded),
        //     otherwise from the beginning of time.
        //   - remaining = maintenance_interval_hours - accumulated_hours
        //   - remaining <= 0                         → Overdue
        //   - 0 < remaining <= limit * 10%           → Upcoming

        $equipments = Equipment::where('is_active', true)
            ->whereNotNull('maintenance_interval_hours')
            ->where('maintenance_interval_hours', '>', 0)
            ->select(['id', 'maintenance_interval_hours', 'last_maintenance'])
            ->get();

        if ($equipments->isEmpty()) {
            $overdueCount  = 0;
            $upcomingCount = 0;
        } else {
            // Build a single query that returns SUM(actual_operating_time) per equipment_id.
            // We filter start_time >= last_maintenance per-row using a CASE expression so the
            // entire calculation is done server-side in one round-trip.
            $equipmentIds        = $equipments->pluck('id')->all();
            $lastMaintenanceMap  = $equipments->pluck('last_maintenance', 'id'); // id => array|null

            // Fetch all relevant operating-time sums grouped by equipment_id.
            // Because last_maintenance cutoff differs per equipment we cannot push the
            // per-row WHERE into a single SQL filter. Instead we pull only the rows we need
            // (scoped to candidate equipment_ids) and aggregate in PHP — still a single query.
            $opTimeSums = DB::table('eamo_operating_times')
                ->whereIn('equipment_id', $equipmentIds)
                ->whereNull('deleted_at')
                ->select('equipment_id', DB::raw('SUM(actual_operating_time) AS total_op'))
                ->groupBy('equipment_id')
                ->get()
                ->keyBy('equipment_id');

            // For equipments whose last_maintenance cutoff is set we need the sum AFTER that date.
            // We aggregate per-equipment in PHP to stay DB-driver agnostic (SQLite compat for tests).
            $withCutoffIds = $equipments
                ->filter(fn ($e) => ! empty($e->last_maintenance['datetime']))
                ->all();

            $opTimeSumsAfterCutoff = collect();
            if (! empty($withCutoffIds)) {
                $afterCutoffMap = [];
                foreach ($withCutoffIds as $equipment) {
                    $cutoff = \Carbon\Carbon::parse($equipment->last_maintenance['datetime']);
                    $sum = DB::table('eamo_operating_times')
                        ->where('equipment_id', $equipment->id)
                        ->whereNull('deleted_at')
                        ->where('start_time', '>=', $cutoff)
                        ->sum('actual_operating_time');
                    $afterCutoffMap[$equipment->id] = (object) ['total_op' => $sum];
                }
                $opTimeSumsAfterCutoff = collect($afterCutoffMap);
            }

            $overdueCount  = 0;
            $upcomingCount = 0;

            foreach ($equipments as $equipment) {
                $limit         = $equipment->maintenance_interval_hours;
                $hasCutoff     = ! empty($equipment->last_maintenance['datetime']);

                if ($hasCutoff) {
                    $actualOp = (float) ($opTimeSumsAfterCutoff->get($equipment->id)->total_op ?? 0);
                } else {
                    $actualOp = (float) ($opTimeSums->get($equipment->id)->total_op ?? 0);
                }

                $remaining = $limit - $actualOp;

                if ($remaining <= 0) {
                    $overdueCount++;
                } elseif ($remaining <= ($limit * 0.1)) {
                    $upcomingCount++;
                }
            }
        }

        $maintenanceTotal = $overdueCount + $upcomingCount;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'total_assets' => [
                    'title'       => 'total_assets',
                    'value'       => $totalAssets,
                    'description' => 'Total number of assets in the system',
                    'icon'        => 'DatabaseOutlined',
                ],
                'active_inactive' => [
                    'title'       => 'active_inactive',
                    'value'       => "{$activeCount} / {$inactiveCount}",
                    'active'      => $activeCount,
                    'inactive'    => $inactiveCount,
                    'description' => 'Ratio of active vs inactive equipment',
                    'icon'        => 'CheckCircleOutlined',
                ],
                'with_errors' => [
                    'title'       => 'with_errors',
                    'value'       => $withErrors,
                    'description' => 'Number of equipments currently recording unhandled error cases',
                    'icon'        => 'WarningOutlined',
                ],
                'maintenance' => [
                    'title'    => 'maintenance',
                    // value = total equipments needing any maintenance action (overdue + upcoming)
                    'value'    => $maintenanceTotal,
                    'overdue'  => $overdueCount,
                    'upcoming' => $upcomingCount,
                    'description' => 'Equipments exceeding or approaching their maintenance cycle limit',
                    'icon'        => 'ClockCircleOutlined',
                ],
            ],
        ]);
    }
}
