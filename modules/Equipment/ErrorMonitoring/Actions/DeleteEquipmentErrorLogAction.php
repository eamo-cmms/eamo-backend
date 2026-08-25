<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

final class DeleteEquipmentErrorLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = EquipmentErrorLog::findOrFail($id);
        Gate::authorize('delete', $log);

        $log->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Equipment error log deleted successfully',
        ]);
    }
}
