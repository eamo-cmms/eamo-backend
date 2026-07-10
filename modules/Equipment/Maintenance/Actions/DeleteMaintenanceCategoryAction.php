<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;

final class DeleteMaintenanceCategoryAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $category = MaintenanceCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Maintenance category deleted successfully.']);
    }
}
