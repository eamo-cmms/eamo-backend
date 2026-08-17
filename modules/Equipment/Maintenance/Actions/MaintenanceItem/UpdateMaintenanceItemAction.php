<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceItem;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceItemRequest;

final class UpdateMaintenanceItemAction
{
    use AsAction;

    public function asController(string $id, StoreMaintenanceItemRequest $request): JsonResponse
    {
        $item = MaintenanceItem::findOrFail($id);

        $item->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        return response()->json($item);
    }
}
