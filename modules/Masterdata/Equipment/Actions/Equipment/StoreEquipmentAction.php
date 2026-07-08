<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\StoreEquipmentRequest;

final class StoreEquipmentAction
{
    use AsAction;

    public function asController(StoreEquipmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        unset($data['work_center_id']); // Safety check: exclude non-db columns if passed

        $equipment = Equipment::create($data);

        if ($request->has('equipment_error_ids')) {
            $equipment->equipmentErrors()->sync($request->input('equipment_error_ids'));
        }

        return response()->json($equipment->load(['equipmentCategory', 'equipmentErrors']), 201);
    }
}
