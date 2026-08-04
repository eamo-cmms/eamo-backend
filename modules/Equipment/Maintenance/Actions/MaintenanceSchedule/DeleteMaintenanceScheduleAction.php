<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceSchedule;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteMaintenanceScheduleAction
{
    use AsAction;

    public function asController(string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $schedule = MaintenanceSchedule::findOrFail($id);

        $cascadeService->deleteMaintenanceSchedule($schedule);

        return response()->json([
            'message' => 'Maintenance schedule deleted successfully.',
        ]);
    }
}
