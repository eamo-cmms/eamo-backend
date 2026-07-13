<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Services;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

final class MaintenanceScheduleGeneratorService
{
    public const MAX_SCHEDULES = 100;

    /**
     * Generate dates based on start date, cycle type, cycle interval, and occurrences.
     *
     * @return CarbonImmutable[]
     */
    public function generateDates(
        CarbonImmutable $startDate,
        string $cycleType,
        int $cycleInterval,
        int $occurrences
    ): array {
        $dates = [];
        for ($i = 0; $i < $occurrences; $i++) {
            $step = $i * $cycleInterval;
            $dates[] = match ($cycleType) {
                'daily' => $startDate->addDays($step),
                'weekly' => $startDate->addWeeks($step),
                'monthly' => $startDate->addMonths($step),
                'yearly' => $startDate->addYears($step),
                default => throw new \InvalidArgumentException("Invalid cycle type: {$cycleType}"),
            };
        }

        return $dates;
    }

    /**
     * Generate schedules for a plan (first-time creation).
     * Sets original_date = date for all new schedules.
     *
     * @throws ValidationException
     */
    public function generateForPlan(MaintenancePlan $plan): void
    {
        if (empty($plan->cycle_type) || empty($plan->cycle_interval) || empty($plan->occurrences)) {
            return;
        }

        $items = MaintenanceItem::with('users')->where('maintenance_category_id', $plan->maintenance_category_id)->get();
        if ($items->isEmpty()) {
            return;
        }

        $occurrences = (int) $plan->occurrences;
        $itemsCount = $items->count();
        $totalSchedules = $occurrences * $itemsCount;

        if ($totalSchedules > self::MAX_SCHEDULES) {
            throw ValidationException::withMessages([
                'occurrences' => ["The total number of expected maintenance schedules ({$totalSchedules}) exceeds the maximum limit of ".self::MAX_SCHEDULES.'.'],
            ]);
        }

        $startDate = CarbonImmutable::parse($plan->date);
        $dates = $this->generateDates($startDate, $plan->cycle_type, (int) $plan->cycle_interval, $occurrences);

        foreach ($dates as $date) {
            $formattedDate = $date->format('Y-m-d');
            foreach ($items as $item) {
                $schedule = MaintenanceSchedule::create([
                    'maintenance_plan_id' => $plan->id,
                    'equipment_id' => $plan->equipment_id,
                    'maintenance_item_id' => $item->id,
                    'date' => $formattedDate,
                    'original_date' => $formattedDate,
                    'is_rescheduled' => false,
                ]);

                $userIds = $item->users->pluck('id')->toArray();
                if (! empty($userIds)) {
                    $schedule->users()->sync($userIds);
                }
            }
        }
    }

    /**
     * Regenerate schedules for a plan, preserving:
     *   1. Schedules that have logs (already executed)
     *   2. Schedules that have been manually rescheduled (is_rescheduled = true)
     *
     * Rescheduled schedules are matched by (item_id, original_date) so they
     * keep their new date while still occupying their original slot.
     *
     * @throws ValidationException
     */
    public function regenerateForPlan(MaintenancePlan $plan): void
    {
        // 1. Collect IDs that must never be deleted: logged OR rescheduled
        $protectedIds = $this->getProtectedScheduleIds($plan);

        // 2. If no cycle info, delete only unprotected schedules and stop
        if (empty($plan->cycle_type) || empty($plan->cycle_interval) || empty($plan->occurrences)) {
            $plan->maintenanceSchedule()->whereNotIn('id', $protectedIds)->delete();

            return;
        }

        // 3. Determine items from the category
        $items = MaintenanceItem::with('users')->where('maintenance_category_id', $plan->maintenance_category_id)->get();
        if ($items->isEmpty()) {
            $plan->maintenanceSchedule()->whereNotIn('id', $protectedIds)->delete();

            return;
        }

        $currentItemIds = $items->pluck('id')->toArray();

        // 4. Delete schedules for items no longer in the category (skip protected)
        $plan->maintenanceSchedule()
            ->where(function ($query) use ($currentItemIds) {
                $query->whereNotIn('maintenance_item_id', $currentItemIds)
                    ->orWhereNull('maintenance_item_id');
            })
            ->whereNotIn('id', $protectedIds)
            ->delete();

        // 5. If cycle fields changed, delete unprotected schedules so they get regenerated
        $cycleFields = ['cycle_type', 'cycle_interval', 'occurrences', 'date'];
        $cycleChanged = false;
        foreach ($cycleFields as $field) {
            if ($plan->isDirty($field) || $plan->wasChanged($field)) {
                $cycleChanged = true;

                break;
            }
        }

        if ($cycleChanged) {
            $plan->maintenanceSchedule()->whereNotIn('id', $protectedIds)->delete();
        }

        // 6. Validate total schedule count
        $occurrences = (int) $plan->occurrences;
        $itemsCount = $items->count();
        $totalNewSchedules = $occurrences * $itemsCount;

        if ($totalNewSchedules > self::MAX_SCHEDULES) {
            throw ValidationException::withMessages([
                'occurrences' => ["The total number of expected maintenance schedules ({$totalNewSchedules}) exceeds the maximum limit of ".self::MAX_SCHEDULES.'.'],
            ]);
        }

        // 7. Generate target dates
        $startDate = CarbonImmutable::parse($plan->date);
        $dates = $this->generateDates($startDate, $plan->cycle_type, (int) $plan->cycle_interval, $occurrences);

        // 8. For each slot (item + date), check if a schedule already occupies it.
        //    Match by original_date so rescheduled records still claim their slot.
        foreach ($dates as $date) {
            $formattedDate = $date->format('Y-m-d');
            foreach ($items as $item) {
                $exists = $plan->maintenanceSchedule()
                    ->where('maintenance_item_id', $item->id)
                    ->where(function ($query) use ($formattedDate) {
                        // Match either by current date (unmodified) or original_date (rescheduled)
                        $query->where('date', $formattedDate)
                            ->orWhere('original_date', $formattedDate);
                    })
                    ->exists();

                if (! $exists) {
                    $schedule = MaintenanceSchedule::create([
                        'maintenance_plan_id' => $plan->id,
                        'equipment_id' => $plan->equipment_id,
                        'maintenance_item_id' => $item->id,
                        'date' => $formattedDate,
                        'original_date' => $formattedDate,
                        'is_rescheduled' => false,
                    ]);

                    $userIds = $item->users->pluck('id')->toArray();
                    if (! empty($userIds)) {
                        $schedule->users()->sync($userIds);
                    }
                }
            }
        }
    }

    /**
     * Get IDs of schedules that should not be deleted during regeneration.
     * Protected = has logs OR is_rescheduled = true.
     *
     * @return string[]
     */
    private function getProtectedScheduleIds(MaintenancePlan $plan): array
    {
        $scheduleIds = $plan->maintenanceSchedule()->pluck('id');

        // Schedules with logs
        $loggedIds = MaintenanceLog::whereIn('maintenance_schedule_id', $scheduleIds)
            ->pluck('maintenance_schedule_id')
            ->toArray();

        // Schedules manually rescheduled
        $rescheduledIds = $plan->maintenanceSchedule()
            ->where('is_rescheduled', true)
            ->pluck('id')
            ->toArray();

        return array_values(array_unique(array_merge($loggedIds, $rescheduledIds)));
    }
}
