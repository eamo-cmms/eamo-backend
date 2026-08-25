<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentError;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentError;

final class DeleteEquipmentErrorAction
{
    use AsAction;

    private const SYSTEM_EQUIPMENT_ERROR_IDS = [
        'emergency_stop',
    ];

    public function asController(Request $request, string $id): JsonResponse
    {
        $error = EquipmentError::findOrFail($id);
        Gate::authorize('delete', $error);

        if (in_array($error->id, self::SYSTEM_EQUIPMENT_ERROR_IDS, strict: true)) {
            return response()->json(['message' => __('equipment.error_system_default_cannot_delete')], 422);
        }

        $error->delete();

        return response()->json(['message' => __('equipment.error_deleted')]);
    }
}
