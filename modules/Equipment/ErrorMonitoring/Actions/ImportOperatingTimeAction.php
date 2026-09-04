<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Requests\ImportOperatingTimeRequest;
use Modules\Equipment\ErrorMonitoring\Services\ImportOperatingTimeService;

final class ImportOperatingTimeAction
{
    use AsAction;

    /**
     * Persist imported operating time records.
     *
     * @param  array<int, array<string, mixed>>  $records
     */
    public function handle(array $records, ?ImportOperatingTimeService $service = null): int
    {
        $service ??= app(ImportOperatingTimeService::class);

        return $service->saveRecords($records);
    }

    public function asController(
        ImportOperatingTimeRequest $request,
        ImportOperatingTimeService $service
    ): JsonResponse {
        $importedCount = $service->saveRecords($request->getValidatedRecords());

        return response()->json([
            'status' => 'success',
            'message' => __('error_monitoring.records_imported_successfully', ['count' => $importedCount]),
        ], 201);
    }
}
