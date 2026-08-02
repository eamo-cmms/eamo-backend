<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceCategory;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteMaintenanceCategoryAction
{
    use AsAction;

    public function asController(string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $category = MaintenanceCategory::findOrFail($id);
        $cascadeService->deleteMaintenanceCategory($category);

        return response()->json(['message' => 'Maintenance category deleted successfully.']);
    }
}
