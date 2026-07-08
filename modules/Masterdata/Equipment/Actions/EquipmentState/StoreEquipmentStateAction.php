<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentState;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentState;
use Modules\Masterdata\Equipment\Requests\EquipmentState\StoreEquipmentStateRequest;

final class StoreEquipmentStateAction
{
    use AsAction;

    public function asController(StoreEquipmentStateRequest $request): JsonResponse
    {
        $state = EquipmentState::create($request->validated());

        return response()->json($state->load('equipment'), 201);
    }
}
