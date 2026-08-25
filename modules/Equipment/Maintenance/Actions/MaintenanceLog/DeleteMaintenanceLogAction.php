<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class DeleteMaintenanceLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = MaintenanceLog::findOrFail($id);
        Gate::authorize('delete', $log);

        $log->delete();

        return response()->json(['success' => true]);
    }
}
