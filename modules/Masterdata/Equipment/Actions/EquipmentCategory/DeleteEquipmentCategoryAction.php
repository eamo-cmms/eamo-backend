<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;

final class DeleteEquipmentCategoryAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $category = EquipmentCategory::findOrFail($id);
        Gate::authorize('delete', $category);

        $category->delete();

        return response()->json(['message' => __('equipment.category_deleted')]);
    }
}
