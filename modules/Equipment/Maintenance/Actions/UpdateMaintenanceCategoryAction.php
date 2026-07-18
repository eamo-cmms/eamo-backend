<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenanceCategoryRequest;
use Throwable;

final class UpdateMaintenanceCategoryAction
{
    use AsAction, SyncsUsersWithNotification;

    /**
     * @throws Throwable
     */
    public function asController(
        string $id,
        UpdateMaintenanceCategoryRequest $request,
        EquipmentCascadeSoftDeleteService $cascadeService
    ): JsonResponse
    {
        $category = MaintenanceCategory::findOrFail($id);
        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (array_key_exists('items', $validated)) {
            $itemsInput = $validated['items'] ?? [];
            $keepIds = collect($itemsInput)->pluck('id')->filter()->values()->toArray();

            // Delete items no longer present
            $category->maintenanceItems()
                ->whereNotIn('id', $keepIds)
                ->get()
                ->each(fn ($item) => $cascadeService->deleteMaintenanceItem($item));

            foreach ($itemsInput as $itemData) {
                if (! empty($itemData['id'])) {
                    // Update existing item
                    $item = $category->maintenanceItems()->findOrFail($itemData['id']);
                    $item->update([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                    ]);
                } else {
                    // Create new item
                    $item = $category->maintenanceItems()->create([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                    ]);
                }

                $userIds = $itemData['user_ids'] ?? [];
                $this->syncUsersAndNotify(
                    $item->users(),
                    $userIds,
                    'maintenance_item',
                    $item->id,
                    $item->name
                );
            }
        }

        return response()->json($category->fresh()->load('maintenanceItems.users'));
    }
}
