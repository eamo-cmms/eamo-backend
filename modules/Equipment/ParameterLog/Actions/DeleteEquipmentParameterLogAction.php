<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

final class DeleteEquipmentParameterLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = EquipmentParameterLog::withTrashed()->find($id);

        if (! $log) {
            return response()->json([
                'status' => 'error',
                'message' => 'Equipment parameter log not found',
            ], 404);
        }

        Gate::authorize('delete', $log);

        $log->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => 'Equipment parameter log deleted successfully',
        ]);
    }
}
