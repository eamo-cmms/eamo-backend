<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceItem;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;

final class DeleteMaintenanceItemAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $item = MaintenanceItem::findOrFail($id);
        Gate::authorize('delete', $item);

        $item->delete();

        return response()->json(['message' => __('maintenance.item_deleted')]);
    }
}
