<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\StandardParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\StandardParameter;

final class ShowStandardParameterAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $parameter = StandardParameter::with(['equipment', 'equipmentParameter'])
            ->findOrFail($id);

        return response()->json($parameter);
    }
}
