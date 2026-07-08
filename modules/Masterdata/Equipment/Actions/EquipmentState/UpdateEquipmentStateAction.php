<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentState;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentState;
use Modules\Masterdata\Equipment\Requests\EquipmentState\UpdateEquipmentStateRequest;

final class UpdateEquipmentStateAction
{
    use AsAction;

    public function asController(UpdateEquipmentStateRequest $request, string $id): JsonResponse
    {
        $state = EquipmentState::findOrFail($id);
        $state->update($request->validated());

        return response()->json($state->load('equipment'));
    }
}
