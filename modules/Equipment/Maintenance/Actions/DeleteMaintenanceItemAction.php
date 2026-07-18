<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;

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
