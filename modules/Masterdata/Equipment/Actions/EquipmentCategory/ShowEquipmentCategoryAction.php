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
        $query = EquipmentCategory::query();
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $category = $query->findOrFail($id);

        return response()->json($category);
    }
}
