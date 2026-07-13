<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class IndexMaintenanceLogAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = MaintenanceLog::query();

        if ($request->filled('maintenance_schedule_id')) {
            $query->where('maintenance_schedule_id', $request->input('maintenance_schedule_id'));
        }

        return response()->json($query->get());
    }
}
