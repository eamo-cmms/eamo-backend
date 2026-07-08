<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentError;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentError;
use Modules\Masterdata\Equipment\Requests\EquipmentError\StoreEquipmentErrorRequest;

final class StoreEquipmentErrorAction
{
    use AsAction;

    public function asController(StoreEquipmentErrorRequest $request): JsonResponse
    {
        $error = EquipmentError::create($request->validated());

        if ($request->has('equipment_ids')) {
            $error->equipment()->sync($request->input('equipment_ids'));
        }

        return response()->json($error->load('equipment'), 201);
    }
}
