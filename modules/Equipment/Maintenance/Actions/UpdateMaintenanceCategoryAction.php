<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenanceCategoryRequest;
use Throwable;

final class UpdateMaintenanceCategoryAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateMaintenanceCategoryRequest $request): JsonResponse
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
            $category->maintenanceItems()->whereNotIn('id', $keepIds)->delete();

            foreach ($itemsInput as $itemData) {
                if (! empty($itemData['id'])) {
                    // Update existing item
                    $category->maintenanceItems()->where('id', $itemData['id'])->update([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                    ]);
                } else {
                    // Create new item
                    $category->maintenanceItems()->create([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                    ]);
                }
            }
        }

        return response()->json($category->fresh()->load('maintenanceItems'));
    }
}
