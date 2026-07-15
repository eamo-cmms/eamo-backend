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
        $relations = [
            'equipmentCategory',
            'equipmentParameters.unit',
            'equipmentErrors',
            'equipmentState',
            'equipmentImages',
        ];

        if ($request->boolean('include_children')) {
            $relations[] = 'children';
        }

        if ($request->boolean('include_parent')) {
            $relations[] = 'parent';
        }

        $equipment = Equipment::with($relations)->findOrFail($id);

        return response()->json($equipment);
    }
}
