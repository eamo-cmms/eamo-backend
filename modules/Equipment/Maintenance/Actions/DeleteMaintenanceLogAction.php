<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class DeleteMaintenanceLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = MaintenanceLog::findOrFail($id);
        $log->delete();

        return response()->json(['success' => true]);
    }
}
