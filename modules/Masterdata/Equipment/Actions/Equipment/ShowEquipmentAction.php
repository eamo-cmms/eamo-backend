<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class ShowEquipmentAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $equipment = Equipment::with([
            'equipmentCategory',
            'equipmentParameters.unit',
            'equipmentErrors',
            'equipmentState',
            'equipmentImages',
        ])->findOrFail($id);

        return response()->json($equipment);
    }
}
