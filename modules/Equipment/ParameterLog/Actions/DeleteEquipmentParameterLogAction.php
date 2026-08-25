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

        abort_unless($log, 404, __('parameter_log.not_found'));

        Gate::authorize('delete', $log);

        $log->forceDelete();

        return response()->json([
            'status' => 'success',
            'message' => __('parameter_log.deleted'),
        ]);
    }
}
