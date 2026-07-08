<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentState;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentState;

final class ShowEquipmentStateAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $state = EquipmentState::findOrFail($id);

        return response()->json($state);
    }
}
