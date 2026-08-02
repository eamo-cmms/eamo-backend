<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Maintenance\Requests\StoreMaintenancePlanRequest;
use Modules\Equipment\Maintenance\Services\MaintenanceScheduleGeneratorService;
use Throwable;

final class StoreMaintenancePlanAction
{
    use AsAction, SyncsUsersWithNotification;

    /**
     * @throws Throwable
     */
    public function asController(
        StoreMaintenancePlanRequest $request,
        MaintenanceScheduleGeneratorService $generatorService
    ): JsonResponse {
        $validated = $request->validated();

        $plan = MaintenancePlan::create([
            'plan_code' => ! empty($validated['plan_code']) ? $validated['plan_code'] : 'PM-'.date('Ymd').'-'.strtoupper(Str::random(6)),
            'equipment_id' => $validated['equipment_id'],
            'maintenance_category_id' => $validated['maintenance_category_id'],
            'maintenance_type' => $validated['maintenance_type'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'cycle_type' => $validated['cycle_type'] ?? null,
            'cycle_interval' => $validated['cycle_interval'] ?? null,
            'occurrences' => $validated['occurrences'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($plan->cycle_type)) {
            $generatorService->generateForPlan($plan);
            if (! empty($validated['schedules'])) {
                foreach ($validated['schedules'] as $scheduleData) {
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
        } else {
            foreach ($validated['schedules'] ?? [] as $scheduleData) {
                $schedule = MaintenanceSchedule::create([
                    'maintenance_plan_id' => $plan->id,
                    'equipment_id' => $plan->equipment_id,
                    'maintenance_item_id' => $scheduleData['maintenance_item_id'],
                    'date' => $scheduleData['date'],
                ]);

                if (! empty($scheduleData['user_ids'])) {
                    $schedule->loadMissing('maintenanceItem');
                    $dateStr = $schedule->date ? (is_string($schedule->date) ? $schedule->date : $schedule->date->format('Y-m-d')) : '';
                    $label = ($schedule->maintenanceItem?->name ?? 'Bảo trì').($dateStr ? " ($dateStr)" : '');
                    $this->syncUsersAndNotify(
                        $schedule->users(),
                        $scheduleData['user_ids'],
                        'maintenance_schedule',
                        $schedule->id,
                        $label
                    );
                }
            }
        }

        return response()->json(
            $plan->load([
                'equipment',
                'maintenanceSchedule.maintenanceItem',
                'maintenanceSchedule.users',
            ]),
            201
        );
    }
}
