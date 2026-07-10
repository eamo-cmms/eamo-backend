<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenancePlanRequest;
use Modules\Equipment\Maintenance\Services\MaintenanceScheduleGeneratorService;
use Throwable;

final class UpdateMaintenancePlanAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(
        string $id,
        UpdateMaintenancePlanRequest $request,
        MaintenanceScheduleGeneratorService $generatorService
    ): JsonResponse {
        $plan = MaintenancePlan::with(['maintenanceSchedule'])->findOrFail($id);

        $validated = $request->validated();

        // Update plan fields (only fields present in request)
        $planFields = array_intersect_key($validated, array_flip([
            'plan_code', 'equipment_id', 'maintenance_type', 'maintenance_category_id',
            'date', 'start_time', 'end_time',
            'cycle_type', 'cycle_interval', 'occurrences', 'notes',
        ]));

        if (! empty($planFields)) {
            $plan->fill($planFields);
        }

        $cycleFields = ['cycle_type', 'cycle_interval', 'occurrences', 'date', 'maintenance_category_id'];
        $shouldRegenerate = false;
        foreach ($cycleFields as $field) {
            if ($plan->isDirty($field)) {
                $shouldRegenerate = true;
            }
        }

        if ($plan->isDirty()) {
            $plan->save();
        }

        if (! empty($plan->cycle_type)) {
            $generatorService->regenerateForPlan($plan);
            if (! empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $scheduleData) {
                    $plan->maintenanceSchedule()
                        ->where('maintenance_item_id', $scheduleData['maintenance_item_id'])
                        ->get()
                        ->each(function ($schedule) use ($scheduleData) {
                            $schedule->users()->sync($scheduleData['user_ids'] ?? []);
                        });
                }
            }
        } elseif (array_key_exists('schedules', $validated) && empty($plan->cycle_type)) {
            $schedulesInput = $validated['schedules'] ?? [];
            $keepIds = collect($schedulesInput)->pluck('id')->filter()->values()->toArray();

            // Delete schedules no longer in the list
            $plan->maintenanceSchedule()->whereNotIn('id', $keepIds)->delete();

            foreach ($schedulesInput as $scheduleData) {
                if (! empty($scheduleData['id'])) {
                    // Update existing schedule
                    $schedule = MaintenanceSchedule::find($scheduleData['id']);
                    if ($schedule) {
                        $schedule->update([
                            'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                            'date' => $scheduleData['date'],
                        ]);
                        $schedule->users()->sync($scheduleData['user_ids'] ?? []);
                    }
                } else {
                    // Create new schedule
                    $schedule = MaintenanceSchedule::create([
                        'maintenance_plan_id' => $plan->id,
                        'equipment_id' => $plan->equipment_id,
                        'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                        'date' => $scheduleData['date'],
                    ]);
                    $schedule->users()->sync($scheduleData['user_ids'] ?? []);
                }
            }
        } else {
            if (! empty($plan->cycle_type) && ! empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $scheduleData) {
                    $plan->maintenanceSchedule()
                        ->where('maintenance_item_id', $scheduleData['maintenance_item_id'])
                        ->get()
                        ->each(function ($schedule) use ($scheduleData) {
                            $schedule->users()->sync($scheduleData['user_ids'] ?? []);
                        });
                }
            }
        }

        return response()->json(
            $plan->fresh()->load([
                'equipment',
                'maintenanceSchedule.maintenanceItem',
                'maintenanceSchedule.users',
            ])
        );
    }
}
