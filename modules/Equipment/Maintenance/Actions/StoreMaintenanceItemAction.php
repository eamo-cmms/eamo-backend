<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceItemRequest;

final class StoreMaintenanceItemAction
{
    use AsAction;

    public function asController(StoreMaintenanceItemRequest $request): JsonResponse
    {
        $item = MaintenanceItem::create([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'maintenance_category_id' => $request->validated('maintenance_category_id'),
        ]);

        if ($request->has('user_ids')) {
            $item->users()->sync($request->validated('user_ids') ?? []);
        }

        return response()->json($item->load('users'), 201);
    }
}
