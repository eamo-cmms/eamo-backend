<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;

final class DeleteMaintenanceCategoryAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $category = MaintenanceCategory::findOrFail($id);
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json(['message' => 'Maintenance category deleted successfully.']);
    }
}
