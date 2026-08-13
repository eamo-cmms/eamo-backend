<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;

final class DeleteEquipmentCategoryAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $category = EquipmentCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Equipment category deleted successfully.']);
    }
}
