<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;

final class UpdateEquipmentErrorsAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validate([
            'equipment_error_ids' => ['required', 'array'],
            'equipment_error_ids.*' => ['string', 'uuid', 'exists:eamo_equipment_errors,id'],
        ]);

        $equipment->equipmentErrors()->sync($data['equipment_error_ids']);

        return response()->json($equipment->load(['equipmentCategory', 'equipmentErrors', 'equipmentState', 'equipmentImages']));
    }
}
