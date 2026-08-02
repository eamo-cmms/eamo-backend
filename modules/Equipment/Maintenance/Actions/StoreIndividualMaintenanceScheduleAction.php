<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Maintenance\Requests\StoreIndividualScheduleRequest;

final class StoreIndividualMaintenanceScheduleAction
{
    use AsAction, SyncsUsersWithNotification;

    public function asController(string $planId, StoreIndividualScheduleRequest $request): JsonResponse
    {
        $plan = MaintenancePlan::findOrFail($planId);
        $validated = $request->validated();

        if ($plan->maintenanceSchedule()->count() >= 100) {
            throw ValidationException::withMessages([
                'plan' => ['Kế hoạch bảo trì này đã đạt giới hạn tối đa 100 mốc lịch.'],
            ]);
        }

        $schedule = DB::transaction(function () use ($plan, $validated) {
            $schedule = MaintenanceSchedule::create([
                'maintenance_plan_id' => $plan->id,
                'equipment_id' => $plan->equipment_id,
                'maintenance_item_id' => $validated['maintenance_item_id'] ?? null,
                'date' => $validated['date'],
                'original_date' => $validated['date'],
                'is_rescheduled' => false,
                'is_auto_generated' => false,
                'is_adhoc' => $validated['is_adhoc'] ?? true,
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! empty($validated['user_ids'])) {
                $this->syncUsersAndNotify(
                    $schedule->users(),
                    $validated['user_ids'],
                    'maintenance_schedule',
                    $schedule->id,
                    "Lịch bảo trì bổ sung ({$schedule->date})"
                );
            }

            return $schedule;
        });

        return response()->json($schedule->load(['equipment', 'maintenanceItem', 'users']), 201);
    }
}
