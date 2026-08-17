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
        $scheduleMode = $data['schedule_mode'] ?? null;
        $isSingleMode = $scheduleMode === 'single' || empty($data['cycle_type']);

        if ($isSingleMode) {
            $data['cycle_type'] = null;
            $data['cycle_interval'] = null;
            $data['occurrences'] = null;
        }

        $plan = MaintenancePlan::create($data);
        $schedules = $data['schedules'] ?? [];
        $planUserIds = $data['user_ids'] ?? [];

        if (! $isSingleMode && ! empty($plan->cycle_type)) {
            $this->generatorService->generateForPlan($plan);

            if (! empty($planUserIds)) {
                MaintenanceScheduleQuery::make()
                    ->forPlan($plan->id)
                    ->get()
                    ->each(fn (MaintenanceSchedule $schedule) => $this->syncScheduleUsers($schedule, $planUserIds));
            }

            foreach ($schedules as $scheduleData) {
                if (! empty($scheduleData['user_ids'])) {
                    MaintenanceScheduleQuery::make()
                        ->forPlan($plan->id)
                        ->forItem($scheduleData['maintenance_item_id'])
                        ->get()
                        ->each(fn (MaintenanceSchedule $schedule) => $this->syncScheduleUsers($schedule, $scheduleData['user_ids']));
                }
            }
        } else {
            if (empty($schedules) && ! empty($plan->maintenance_category_id)) {
                $categoryItems = \Modules\Equipment\Maintenance\Models\MaintenanceItem::where('maintenance_category_id', $plan->maintenance_category_id)->get();
                foreach ($categoryItems as $item) {
                    $schedule = MaintenanceSchedule::create([
                        'maintenance_plan_id' => $plan->id,
                        'equipment_id' => $plan->equipment_id,
                        'maintenance_item_id' => $item->id,
                        'date' => $plan->date,
                        'original_date' => $plan->date,
                        'is_rescheduled' => false,
                    ]);

                    if (! empty($planUserIds)) {
                        $this->syncScheduleUsers($schedule, $planUserIds);
                    }
                }
            } else {
                foreach ($schedules as $scheduleData) {
                    $schedule = MaintenanceSchedule::create([
                        'maintenance_plan_id' => $plan->id,
                        'equipment_id' => $plan->equipment_id,
                        'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                        'date' => $scheduleData['date'],
                        'original_date' => $scheduleData['date'],
                        'is_rescheduled' => false,
                    ]);

                    $targetUserIds = ! empty($scheduleData['user_ids']) ? $scheduleData['user_ids'] : $planUserIds;
                    $this->syncScheduleUsers($schedule, $targetUserIds);
                }
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
