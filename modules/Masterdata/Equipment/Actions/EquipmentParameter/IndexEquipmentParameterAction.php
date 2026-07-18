<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;

final class IndexEquipmentParameterAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = EquipmentParameter::with(['equipment', 'equipmentCategory', 'unit']);

        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        if ($request->has('equipment_id')) {
            $query->where('equipment_id', $request->input('equipment_id'));
        }

        $parameters = $query->paginate($request->integer('per_page', 100));

        return response()->json($parameters);
    }
}
