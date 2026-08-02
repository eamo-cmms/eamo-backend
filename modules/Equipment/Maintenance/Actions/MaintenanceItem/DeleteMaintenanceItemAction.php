<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceItem;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;

final class DeleteMaintenanceItemAction
{
    use AsAction;

    public function asController(string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $item = MaintenanceItem::findOrFail($id);
        $cascadeService->deleteMaintenanceItem($item);

        return response()->json(['message' => 'Maintenance item deleted successfully.']);
    }
}
