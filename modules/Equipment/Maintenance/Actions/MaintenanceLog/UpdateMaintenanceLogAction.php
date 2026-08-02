<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class UpdateMaintenanceLogAction
{
    use AsAction;

    public function asController(string $id, Request $request): JsonResponse
    {
        $log = MaintenanceLog::findOrFail($id);

        $validated = $request->validate([
            'result' => 'required|string|in:Completed,Partial,Failed',
            'note' => 'nullable|string',
        ]);

        $log->update($validated);

        return response()->json($log);
    }
}
