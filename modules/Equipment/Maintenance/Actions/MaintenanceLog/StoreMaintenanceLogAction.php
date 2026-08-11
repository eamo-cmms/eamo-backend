<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Equipment\Maintenance\Requests\StoreMaintenanceLogRequest;

final class StoreMaintenanceLogAction
{
    use AsAction;

    public function asController(StoreMaintenanceLogRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['log_date'] = now()->toDateString();

        $log = MaintenanceLog::create($validated);

        return response()->json($log, 201);
    }
}
