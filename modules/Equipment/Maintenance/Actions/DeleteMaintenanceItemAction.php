<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;

final class DeleteMaintenanceItemAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $item = MaintenanceItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Maintenance item deleted successfully.']);
    }
}
