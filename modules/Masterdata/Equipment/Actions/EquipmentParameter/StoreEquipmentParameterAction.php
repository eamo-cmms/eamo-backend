<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentParameter;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;
use Modules\Masterdata\Equipment\Requests\EquipmentParameter\StoreEquipmentParameterRequest;

final class StoreEquipmentParameterAction
{
    use AsAction;

    public function asController(StoreEquipmentParameterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $parameter = EquipmentParameter::create($validated);

        return response()->json($parameter, 201);
    }
}
