<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Throwable;

final class JudgeMaintenancePlanService
{
    /**
     * Execute batch judging of maintenance schedules.
     *
     * @param  array<string, mixed>  $data
     * @return array{message: string, logs: array<int, MaintenanceLog>}
     *
     * @throws Throwable
     */
    public function execute(array $data, ?User $currentUser): array
    {
        $planId = $data['plan_id'];
        $plan = MaintenancePlan::findOrFail($planId);

        $dateString = ! empty($data['timestamp'])
            ? Carbon::parse($data['timestamp'])->toDateString()
            : Carbon::today()->toDateString();

        $logs = DB::transaction(function () use ($dateString, $data, $currentUser): array {
            $createdLogs = [];
            foreach ($data['results'] as $item) {
                $schedule = MaintenanceSchedule::findOrFail($item['schedule_id']);

                // Find existing log for this schedule on the log date, or create one
                $log = $schedule->maintenanceLogs()
                    ->whereDate('log_date', $dateString)
                    ->first();

                if (! $log) {
                    $log = $schedule->maintenanceLogs()->first();
                }

                $userId = $currentUser?->id;
                if (! empty($data['user_ids']) && is_array($data['user_ids'])) {
                    $userId = $data['user_ids'][0] ?? $userId;
                }

                if (! $log) {
                    $log = $schedule->maintenanceLogs()->create([
                        'equipment_id' => $schedule->equipment_id,
                        'user_id' => $userId,
                        'log_date' => $dateString,
                    ]);
                }

                $logDate = ! empty($data['timestamp'])
                    ? Carbon::parse($data['timestamp'])
                    : Carbon::now();

                $log->update([
                    'equipment_id' => $schedule->equipment_id,
                    'user_id' => $userId,
                    'log_date' => $logDate,
                    'note' => $item['note'] ?? null,
                ]);

                // Update schedule actual date
                $schedule->update([
                    'date' => $dateString,
                ]);

                // Sync users to schedule if provided
                if (isset($data['user_ids'])) {
                    $schedule->users()->sync($data['user_ids']);
                }

                $createdLogs[] = $log;
            }

            return $createdLogs;
        });

        return [
            'message' => 'Maintenance plan schedules judged successfully.',
            'logs' => $logs,
        ];
    }
}
