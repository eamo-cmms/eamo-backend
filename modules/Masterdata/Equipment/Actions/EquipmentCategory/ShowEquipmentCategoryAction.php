<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;

final class ShowEquipmentCategoryAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $category = EquipmentCategory::findOrFail($id);

        return response()->json($category);
    }
}
