<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

final class DeleteEquipmentParameterLogAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $log = EquipmentParameterLog::findOrFail($id);
        $log->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Equipment parameter log deleted successfully',
        ]);
    }
}
