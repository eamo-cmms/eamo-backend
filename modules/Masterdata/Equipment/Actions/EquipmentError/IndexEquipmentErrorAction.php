<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\EquipmentError;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\EquipmentError;

final class IndexEquipmentErrorAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $errors = EquipmentError::paginate($request->integer('per_page', 15));

        return response()->json($errors);
    }
}
