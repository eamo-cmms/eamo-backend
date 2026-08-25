<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\UpdateEquipmentRequest;
use Modules\Masterdata\Equipment\Services\SyncEquipmentErrorsService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentImagesService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentParametersService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentStateService;

final class UpdateEquipmentAction
{
    use AsAction;

    public function asController(
        UpdateEquipmentRequest $request,
        string $id,
        SyncEquipmentStateService $stateService,
        SyncEquipmentImagesService $imagesService,
        SyncEquipmentErrorsService $errorsService,
        SyncEquipmentParametersService $parametersService
    ): JsonResponse {
        $equipment = Equipment::findOrFail($id);

        $data = $request->validated();
        $equipmentData = array_diff_key($data, array_flip(['equipment_parameters', 'state', 'uploaded_images', 'existing_image_ids']));

        $equipment->update($equipmentData);

        if ($request->has('state') && $request->filled('state')) {
            $stateService->set($equipment, $request->input('state'));
        }

        if ($request->has('existing_image_ids') || $request->hasFile('uploaded_images')) {
            $existingImageIds = $request->input('existing_image_ids', []);
            $newFiles = $request->file('uploaded_images', []);
            $imagesService->sync($equipment, $existingImageIds, $newFiles);
        }

        if ($request->has('equipment_error_ids')) {
            $errorsService->sync($equipment, $request->input('equipment_error_ids') ?? []);
        }

        if ($request->has('equipment_parameters')) {
            $parametersService->sync($equipment, $request->input('equipment_parameters') ?? []);
        }

        return response()->json(
            $equipment->load(['equipmentCategory', 'equipmentErrors', 'equipmentParameters.unit', 'equipmentState', 'equipmentImages'])
        );
    }
}

