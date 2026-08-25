<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Requests\ImportEquipmentParameterLogRequest;
use Modules\Equipment\ParameterLog\Services\ImportEquipmentParameterLogService;

final class ImportEquipmentParameterLogAction
{
    use AsAction;

    public function asController(
        ImportEquipmentParameterLogRequest $request,
        ImportEquipmentParameterLogService $service
    ): JsonResponse {
        $importedCount = $service->saveRecords($request->getValidatedRecords());

        return response()->json([
            'status' => 'success',
            'message' => __('parameter_log.records_imported_successfully', ['count' => $importedCount]),
        ], 201);
    }
}
