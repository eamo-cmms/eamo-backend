<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceLogRequest;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Masterdata\Equipment\Models\Equipment;

final class StoreMaintenanceLogAction
{
    use AsAction;

    /**
     * Business logic to store maintenance log and update equipment last_maintenance.
     *
     * @param array<string, mixed> $data
     */
    public function handle(array $data, ?User $user = null): MaintenanceLog
    {
        if (empty($data['log_date'])) {
            $data['log_date'] = now()->toDateString();
        }

        if (empty($data['user_id']) && $user) {
            $data['user_id'] = $user->id;
        }

        if (empty($data['equipment_id']) && ! empty($data['maintenance_schedule_id'])) {
            $schedule = MaintenanceSchedule::find($data['maintenance_schedule_id']);
            if ($schedule) {
                $data['equipment_id'] = $schedule->equipment_id;
            }
        }

        $log = MaintenanceLog::create($data);
        $log->load(['equipment.equipmentCategory', 'user', 'maintenanceSchedule.maintenancePlan']);

        if (! empty($log->equipment_id)) {
            $equipment = Equipment::find($log->equipment_id);
            if ($equipment) {
                $equipment->update([
                    'last_maintenance' => [
                        'equipment_id' => $log->equipment_id,
                        'maintenance_schedule_id' => $log->maintenance_schedule_id,
                        'datetime' => $data['log_date'] ?? now()->toDateTimeString(),
                        'user_id' => $log->user_id,
                        'note' => $log->note,
                    ],
                ]);
            }
        }

        return $log;
    }

    public function asController(StoreMaintenanceLogRequest $request): JsonResponse
    {
        $log = $this->handle($request->validated(), $request->user());

        return response()->json($log, 201);
    }
}
