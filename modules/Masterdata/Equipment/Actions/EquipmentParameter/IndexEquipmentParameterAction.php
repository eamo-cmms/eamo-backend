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
        $parameters = EquipmentParameter::with(['equipment', 'equipmentCategory'])
            ->paginate($request->integer('per_page', 15));

        return response()->json($parameters);
    }
}
