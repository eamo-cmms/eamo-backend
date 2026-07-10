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
     * Generate schedules for a plan.
     *
     * @throws ValidationException
     */
    public function generateForPlan(MaintenancePlan $plan): void
    {
        if (empty($plan->cycle_type) || empty($plan->cycle_interval) || empty($plan->occurrences)) {
            return;
        }

        $items = MaintenanceItem::where('maintenance_category_id', $plan->maintenance_category_id)->get();
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
            foreach ($items as $item) {
                MaintenanceSchedule::create([
                    'maintenance_plan_id' => $plan->id,
                    'equipment_id' => $plan->equipment_id,
                    'maintenance_item_id' => $item->id,
                    'date' => $date->format('Y-m-d'),
                ]);
            }
        }
    }

    /**
     * Regenerate schedules for a plan, keeping schedules that already have logs.
     *
     * @throws ValidationException
     */
    public function regenerateForPlan(MaintenancePlan $plan): void
    {
        // 1. Find schedules associated with this plan that have logs
        $loggedScheduleIds = MaintenanceLog::whereIn(
            'maintenance_schedule_id',
            $plan->maintenanceSchedule()->pluck('id')
        )->pluck('maintenance_schedule_id')->toArray();

        // 2. If there are no cycle info details, we do not auto-generate new ones
        if (empty($plan->cycle_type) || empty($plan->cycle_interval) || empty($plan->occurrences)) {
            $plan->maintenanceSchedule()->whereNotIn('id', $loggedScheduleIds)->delete();

            return;
        }

        // 3. Determine items to generate
        $items = MaintenanceItem::where('maintenance_category_id', $plan->maintenance_category_id)->get();
        if ($items->isEmpty()) {
            $plan->maintenanceSchedule()->whereNotIn('id', $loggedScheduleIds)->delete();

            return;
        }

        $currentItemIds = $items->pluck('id')->toArray();

        // 4. Delete schedules of items that no longer exist in the category or have a null item_id
        $plan->maintenanceSchedule()
            ->where(function ($query) use ($currentItemIds) {
                $query->whereNotIn('maintenance_item_id', $currentItemIds)
                    ->orWhereNull('maintenance_item_id');
            })
            ->whereNotIn('id', $loggedScheduleIds)
            ->delete();

        // 5. If the cycle fields have changed, delete all remaining non-logged schedules so they get regenerated
        $cycleFields = ['cycle_type', 'cycle_interval', 'occurrences', 'date'];
        $cycleChanged = false;
        foreach ($cycleFields as $field) {
            if ($plan->isDirty($field)) {
                $cycleChanged = true;
                break;
            }
        }
        if ($cycleChanged) {
            $plan->maintenanceSchedule()->whereNotIn('id', $loggedScheduleIds)->delete();
        }

        $occurrences = (int) $plan->occurrences;
        $itemsCount = $items->count();
        $totalNewSchedules = $occurrences * $itemsCount;

        if ($totalNewSchedules > self::MAX_SCHEDULES) {
            throw ValidationException::withMessages([
                'occurrences' => ["The total number of expected maintenance schedules ({$totalNewSchedules}) exceeds the maximum limit of ".self::MAX_SCHEDULES.'.'],
            ]);
        }

        // 6. Generate target schedule entries (date + item_id)
        $startDate = CarbonImmutable::parse($plan->date);
        $dates = $this->generateDates($startDate, $plan->cycle_type, (int) $plan->cycle_interval, $occurrences);

        // 7. For each target, check if any schedule already exists matching the item and date.
        // If it exists, keep it. Otherwise, create a new one.
        foreach ($dates as $date) {
            $formattedDate = $date->format('Y-m-d');
            foreach ($items as $item) {
                $exists = $plan->maintenanceSchedule()
                    ->where('maintenance_item_id', $item->id)
                    ->where('date', $formattedDate)
                    ->exists();

                if (! $exists) {
                    MaintenanceSchedule::create([
                        'maintenance_plan_id' => $plan->id,
                        'equipment_id' => $plan->equipment_id,
                        'maintenance_item_id' => $item->id,
                        'date' => $formattedDate,
                    ]);
                }
            }
        }
    }
}
