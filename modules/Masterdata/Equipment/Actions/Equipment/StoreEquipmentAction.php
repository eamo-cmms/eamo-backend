<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
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
        $equipmentData = array_diff_key($data, array_flip(['equipment_parameters', 'state', 'uploaded_images']));

        $equipment = RegisterDeviceWithQrAction::run($equipmentData);

        if ($request->has('state')) {
            $equipment->equipmentState()->create([
                'state' => $request->input('state'),
            ]);
        }

        if ($request->hasFile('uploaded_images')) {
            foreach ($request->file('uploaded_images') as $file) {
                $path = $file->store('equipment_images', 'public');
                $equipment->equipmentImages()->create([
                    'image_id' => (string) Str::uuid(),
                    'path' => '/storage/'.$path,
                ]);
            }
        }

        if ($request->has('equipment_error_ids')) {
            $equipment->equipmentErrors()->sync($request->input('equipment_error_ids'));
        }

        if ($request->has('equipment_parameters')) {
            foreach ($request->input('equipment_parameters') as $param) {
                if (! empty($param['code']) && ! empty($param['name'])) {
                    $equipment->equipmentParameters()->create([
                        'code' => $param['code'],
                        'name' => $param['name'],
                        'unit_id' => $param['unit_id'] ?? null,
                        'equipment_category_id' => $equipment->equipment_category_id,
                    ]);
                }
            }
        }

        return response()->json($equipment->load(['equipmentCategory', 'equipmentErrors', 'equipmentParameters.unit', 'equipmentState', 'equipmentImages']), 201);
    }
}
