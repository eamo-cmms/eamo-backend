<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;

final class DeleteEquipmentParameterAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $parameter = EquipmentParameter::findOrFail($id);
        Gate::authorize('delete', $parameter);

        $parameter->delete();

        return response()->json(['message' => __('equipment.parameter_deleted')]);
    }
}
