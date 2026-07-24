<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Services;

use App\Concerns\SyncsUsersWithNotification;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Maintenance\Queries\MaintenanceScheduleQuery;
use Throwable;

final class StoreMaintenancePlanService
{
    use SyncsUsersWithNotification;

    public function __construct(
        private readonly MaintenanceScheduleGeneratorService $generatorService
    ) {}

    /**
     * Store a maintenance plan and handle schedule generation/creation.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function execute(array $data): MaintenancePlan
    {
        $plan = MaintenancePlan::create($data);
        $schedules = $data['schedules'] ?? [];

        if ($plan->cycle_type) {
            $this->generatorService->generateForPlan($plan);

            foreach ($schedules as $scheduleData) {
                MaintenanceScheduleQuery::make()
                    ->forPlan($plan->id)
                    ->forItem($scheduleData['maintenance_item_id'])
                    ->get()
                    ->each(fn (MaintenanceSchedule $schedule) => $this->syncScheduleUsers($schedule, $scheduleData['user_ids'] ?? []));
            }
        } else {
            foreach ($schedules as $scheduleData) {
                $schedule = MaintenanceSchedule::create([
                    'maintenance_plan_id' => $plan->id,
                    'equipment_id' => $plan->equipment_id,
                    'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                    'date' => $scheduleData['date'],
                ]);

                $this->syncScheduleUsers($schedule, $scheduleData['user_ids'] ?? []);
            }
        }

        return $plan;
    }

    /**
     * Sync users to the schedule and send notifications.
     *
     * @param  array<int, string>  $userIds
     */
    private function syncScheduleUsers(MaintenanceSchedule $schedule, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $this->syncUsersAndNotify(
            $schedule->users(),
            $userIds,
            'maintenance_schedule',
            $schedule->id,
            $schedule->getNotificationLabel()
        );
    }
}
