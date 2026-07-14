<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceCategoryRequest;
use Throwable;

final class StoreMaintenanceCategoryAction
{
    use AsAction, SyncsUsersWithNotification;

    /**
     * @throws Throwable
     */
    public function asController(StoreMaintenanceCategoryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $category = MaintenanceCategory::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                $item = $category->maintenanceItems()->create([
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                ]);

                if (! empty($itemData['user_ids'])) {
                    $this->syncUsersAndNotify(
                        $item->users(),
                        $itemData['user_ids'],
                        'maintenance_item',
                        $item->id,
                        $item->name
                    );
                }
            }
        }

        return response()->json($category->load('maintenanceItems.users'), 201);
    }
}
