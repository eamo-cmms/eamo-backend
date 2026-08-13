<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentError;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Masterdata\Equipment\Models\EquipmentError;

final class DeleteEquipmentErrorAction
{
    use AsAction;

    private const SYSTEM_EQUIPMENT_ERROR_IDS = [
        'emergency_stop',
    ];

    public function asController(Request $request, string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $error = EquipmentError::findOrFail($id);

        if (in_array($error->id, self::SYSTEM_EQUIPMENT_ERROR_IDS, strict: true)) {
            return response()->json(['message' => 'This equipment error is a system default and cannot be deleted.'], 422);
        }

        $cascadeService->deleteEquipmentError($error);

        return response()->json(['message' => 'Equipment error deleted successfully.']);
    }
}
