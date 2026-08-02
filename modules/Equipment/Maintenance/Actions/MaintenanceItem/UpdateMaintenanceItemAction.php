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
    use AsAction, SyncsUsersWithNotification;

    public function asController(string $id, StoreMaintenanceItemRequest $request): JsonResponse
    {
        $item = MaintenanceItem::findOrFail($id);

        $item->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ]);

        if ($request->has('user_ids')) {
            $this->syncUsersAndNotify(
                $item->users(),
                $request->validated('user_ids') ?? [],
                'maintenance_item',
                $item->id,
                $item->name
            );
        }

        return response()->json($item->load('users'));
    }
}
