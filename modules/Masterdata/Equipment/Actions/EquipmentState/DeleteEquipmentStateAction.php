<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentState;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentState;

final class DeleteEquipmentStateAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $state = EquipmentState::findOrFail($id);
        $state->delete();

        return response()->json(['message' => 'Equipment state deleted successfully.']);
    }
}
