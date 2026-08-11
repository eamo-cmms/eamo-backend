<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Equipment\Maintenance\Requests\UpdateMaintenanceLogRequest;

final class UpdateMaintenanceLogAction
{
    use AsAction;

    public function asController(string $id, UpdateMaintenanceLogRequest $request): JsonResponse
    {
        $log = MaintenanceLog::findOrFail($id);
        $log->update($request->validated());

        return response()->json($log);
    }
}
