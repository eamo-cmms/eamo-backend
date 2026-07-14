<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Services;

use App\Concerns\SyncsUsersWithNotification;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Throwable;

final class MaintenancePlanUpdateService
{
    use SyncsUsersWithNotification;

    public function __construct(
        private readonly MaintenanceScheduleGeneratorService $generatorService
    ) {}

    /**
     * Update a maintenance plan and handle schedule regeneration/updating.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Throwable
     */
    public function update(MaintenancePlan $plan, array $data): MaintenancePlan
    {
        // 1. Filter and fill plan attributes
        $planFields = array_intersect_key($data, array_flip([
            'plan_code', 'equipment_id', 'maintenance_type', 'maintenance_category_id',
            'date', 'start_time', 'end_time',
            'cycle_type', 'cycle_interval', 'occurrences', 'notes',
        ]));

        if (! empty($planFields)) {
            $plan->fill($planFields);
        }

        if ($plan->isDirty()) {
            $plan->save();
        }

        // 2. Handle schedules update
        if (! empty($plan->cycle_type)) {
            // First, process any date changes to existing schedules before regenerating, so they are marked is_rescheduled
            if (! empty($data['schedules'])) {
                foreach ($data['schedules'] as $scheduleData) {
                    if (! empty($scheduleData['id'])) {
                        $schedule = MaintenanceSchedule::find($scheduleData['id']);
                        if ($schedule && $schedule->date !== $scheduleData['date']) {
                            $updateData = [
                                'date' => $scheduleData['date'],
                                'is_rescheduled' => true,
                            ];
                            if (empty($schedule->original_date)) {
                                $updateData['original_date'] = $schedule->date;
                            }
                            $schedule->update($updateData);
                        }
                    }
                }
            }

            // Regenerate plans (keeping protected ones)
            $this->generatorService->regenerateForPlan($plan);

            // Sync users for each schedule (either specific by id, or bulk fallback by item)
            if (! empty($data['schedules'])) {
                foreach ($data['schedules'] as $scheduleData) {
                    if (! empty($scheduleData['id'])) {
                        $schedule = MaintenanceSchedule::find($scheduleData['id']);
                        if ($schedule) {
                            $schedule->loadMissing('maintenanceItem');
                            $dateStr = $schedule->date ? (is_string($schedule->date) ? $schedule->date : $schedule->date->format('Y-m-d')) : '';
                            $label = ($schedule->maintenanceItem?->name ?? 'Bảo trì').($dateStr ? " ($dateStr)" : '');
                            $this->syncUsersAndNotify(
                                $schedule->users(),
                                $scheduleData['user_ids'] ?? [],
                                'maintenance_schedule',
                                $schedule->id,
                                $label
                            );
                        }
                    } else {
                        $plan->maintenanceSchedule()
                            ->where('maintenance_item_id', $scheduleData['maintenance_item_id'])
                            ->get()
                            ->each(function ($schedule) use ($scheduleData) {
                                $schedule->loadMissing('maintenanceItem');
                                $dateStr = $schedule->date ? (is_string($schedule->date) ? $schedule->date : $schedule->date->format('Y-m-d')) : '';
                                $label = ($schedule->maintenanceItem?->name ?? 'Bảo trì').($dateStr ? " ($dateStr)" : '');
                                $this->syncUsersAndNotify(
                                    $schedule->users(),
                                    $scheduleData['user_ids'] ?? [],
                                    'maintenance_schedule',
                                    $schedule->id,
                                    $label
                                );
                            });
                    }
                }
            }
        } elseif (array_key_exists('schedules', $data)) {
            $schedulesInput = $data['schedules'] ?? [];
            $keepIds = collect($schedulesInput)->pluck('id')->filter()->values()->toArray();

            // Delete schedules no longer in the list
            $plan->maintenanceSchedule()->whereNotIn('id', $keepIds)->delete();

            foreach ($schedulesInput as $scheduleData) {
                if (! empty($scheduleData['id'])) {
                    // Update existing schedule
                    $schedule = MaintenanceSchedule::find($scheduleData['id']);
                    if ($schedule) {
                        $updateData = [
                            'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                            'date' => $scheduleData['date'],
                        ];

                        // If date changed, mark as rescheduled and preserve original_date
                        if ($schedule->date !== $scheduleData['date']) {
                            $updateData['is_rescheduled'] = true;
                            if (empty($schedule->original_date)) {
                                $updateData['original_date'] = $schedule->date;
                            }
                        }

                        $schedule->update($updateData);
                        $schedule->loadMissing('maintenanceItem');
                        $dateStr = $schedule->date ? (is_string($schedule->date) ? $schedule->date : $schedule->date->format('Y-m-d')) : '';
                        $label = ($schedule->maintenanceItem?->name ?? 'Bảo trì').($dateStr ? " ($dateStr)" : '');
                        $this->syncUsersAndNotify(
                            $schedule->users(),
                            $scheduleData['user_ids'] ?? [],
                            'maintenance_schedule',
                            $schedule->id,
                            $label
                        );
                    }
                } else {
                    // Create new schedule
                    $schedule = MaintenanceSchedule::create([
                        'maintenance_plan_id' => $plan->id,
                        'equipment_id' => $plan->equipment_id,
                        'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                        'date' => $scheduleData['date'],
                        'original_date' => $scheduleData['date'],
                        'is_rescheduled' => false,
                    ]);
                    $schedule->loadMissing('maintenanceItem');
                    $dateStr = $schedule->date ? (is_string($schedule->date) ? $schedule->date : $schedule->date->format('Y-m-d')) : '';
                    $label = ($schedule->maintenanceItem?->name ?? 'Bảo trì').($dateStr ? " ($dateStr)" : '');
                    $this->syncUsersAndNotify(
                        $schedule->users(),
                        $scheduleData['user_ids'] ?? [],
                        'maintenance_schedule',
                        $schedule->id,
                        $label
                    );
                }
            }
        }

        return $plan->fresh()->load([
            'equipment',
            'maintenanceSchedule.maintenanceItem',
            'maintenanceSchedule.users',
        ]);
    }
}
