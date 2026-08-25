<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\StoreEquipmentRequest;
use Modules\Masterdata\Equipment\Services\SyncEquipmentErrorsService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentImagesService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentParametersService;
use Modules\Masterdata\Equipment\Services\SyncEquipmentStateService;

final class StoreEquipmentAction
{
    use AsAction;

    public function asController(
        StoreEquipmentRequest $request,
        SyncEquipmentStateService $stateService,
        SyncEquipmentImagesService $imagesService,
        SyncEquipmentErrorsService $errorsService,
        SyncEquipmentParametersService $parametersService
    ): JsonResponse {
        $data = $request->validated();
        $equipmentData = array_diff_key($data, array_flip(['equipment_parameters', 'state', 'uploaded_images']));

        $equipmentData['last_maintenance'] = [
            'datetime' => now()->toIso8601String(),
        ];

        $equipment = RegisterDeviceWithQrAction::run($equipmentData);

        if ($request->has('state') && $request->filled('state')) {
            $stateService->create($equipment, $request->input('state'));
        }

        if ($request->hasFile('uploaded_images')) {
            $imagesService->uploadImages($equipment, $request->file('uploaded_images'));
        }

        if ($request->has('equipment_error_ids')) {
            $errorsService->sync($equipment, $request->input('equipment_error_ids') ?? []);
        }

        if ($request->has('equipment_parameters')) {
            $parametersService->create($equipment, $request->input('equipment_parameters') ?? []);
        }

        return response()->json(
            $equipment->load(['equipmentCategory', 'equipmentErrors', 'equipmentParameters.unit', 'equipmentState', 'equipmentImages']),
            201
        );
    }
}

