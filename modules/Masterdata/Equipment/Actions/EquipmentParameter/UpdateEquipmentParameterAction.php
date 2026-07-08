<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentParameter;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;
use Modules\Masterdata\Equipment\Requests\EquipmentParameter\UpdateEquipmentParameterRequest;

final class UpdateEquipmentParameterAction
{
    use AsAction;

    public function asController(UpdateEquipmentParameterRequest $request, string $id): JsonResponse
    {
        $parameter = EquipmentParameter::findOrFail($id);
        $parameter->update($request->validated());

        return response()->json($parameter);
    }
}
