<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;

final class DeleteEquipmentParameterAction
{
    use AsAction;

    public function asController(Request $request, string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $parameter = EquipmentParameter::findOrFail($id);
        $cascadeService->deleteParameter($parameter);

        return response()->json(['message' => 'Equipment parameter deleted successfully.']);
    }
}
