<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentState;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentState;

final class IndexEquipmentStateAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $states = EquipmentState::paginate($request->integer('per_page', 15));

        return response()->json($states);
    }
}
