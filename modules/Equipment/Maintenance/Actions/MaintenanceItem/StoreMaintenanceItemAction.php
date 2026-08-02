<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceItem;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceItemRequest;

final class StoreMaintenanceItemAction
{
    use AsAction, SyncsUsersWithNotification;

    public function asController(StoreMaintenanceItemRequest $request): JsonResponse
    {
        $item = MaintenanceItem::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'maintenance_category_id' => $request->validated('maintenance_category_id'),
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

        return response()->json($item->load('users'), 201);
    }
}
