<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\UpdateEquipmentRequest;

final class UpdateEquipmentAction
{
    use AsAction;

    public function asController(UpdateEquipmentRequest $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validated();
        unset($data['work_center_id']); // Safety check: exclude non-db columns if passed

        $equipment->update($data);

        if ($request->has('equipment_error_ids')) {
            $equipment->equipmentErrors()->sync($request->input('equipment_error_ids'));
        }

        return response()->json($equipment->load(['equipmentCategory', 'equipmentErrors']));
    }
}
