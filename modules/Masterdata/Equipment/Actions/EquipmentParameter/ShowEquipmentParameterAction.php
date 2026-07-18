<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;

final class ShowEquipmentParameterAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $query = EquipmentParameter::with([
            'equipment',
            'equipmentCategory',
            'unit',
        ]);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $parameter = $query->findOrFail($id);

        return response()->json($parameter);
    }
}
