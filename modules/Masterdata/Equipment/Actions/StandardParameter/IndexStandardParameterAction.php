<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\StandardParameter;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\StandardParameter;

final class IndexStandardParameterAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $parameters = StandardParameter::with(['equipment', 'equipmentParameter'])
            ->paginate($request->integer('per_page', 15));

        return response()->json($parameters);
    }
}
