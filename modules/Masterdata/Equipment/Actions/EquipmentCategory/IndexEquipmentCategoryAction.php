<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;

final class IndexEquipmentCategoryAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = EquipmentCategory::query();
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $categories = $query->paginate($request->integer('per_page', 15));

        return response()->json($categories);
    }
}
