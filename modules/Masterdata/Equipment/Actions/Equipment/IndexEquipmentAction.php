<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class IndexEquipmentAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $equipment = Equipment::with(['equipmentCategory'])
            ->paginate($request->integer('per_page', 15));

        return response()->json($equipment);
    }
}
