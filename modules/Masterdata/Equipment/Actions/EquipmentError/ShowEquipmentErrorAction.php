<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentError;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentError;

final class ShowEquipmentErrorAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $error = EquipmentError::findOrFail($id);

        return response()->json($error);
    }
}
