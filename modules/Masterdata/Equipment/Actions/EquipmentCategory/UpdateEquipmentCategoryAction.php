<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentCategory;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;
use Modules\Masterdata\Equipment\Requests\EquipmentCategory\UpdateEquipmentCategoryRequest;

final class UpdateEquipmentCategoryAction
{
    use AsAction;

    public function asController(UpdateEquipmentCategoryRequest $request, string $id): JsonResponse
    {
        $category = EquipmentCategory::findOrFail($id);
        $category->update($request->validated());

        return response()->json($category);
    }
}
