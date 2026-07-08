<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentCategory;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;
use Modules\Masterdata\Equipment\Requests\EquipmentCategory\StoreEquipmentCategoryRequest;

final class StoreEquipmentCategoryAction
{
    use AsAction;

    public function asController(StoreEquipmentCategoryRequest $request): JsonResponse
    {
        $category = EquipmentCategory::create($request->validated());

        return response()->json($category, 201);
    }
}
