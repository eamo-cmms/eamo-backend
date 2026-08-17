<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceCategory;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceCategoryRequest;
use Throwable;

final class StoreMaintenanceCategoryAction
{
    use AsAction;

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
                $category->maintenanceItems()->create([
                    'name' => $itemData['name'],
                    'description' => $itemData['description'] ?? null,
                ]);
            }
        }

        return response()->json($category->load('maintenanceItems'), 201);
    }
}
