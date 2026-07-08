<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\StandardParameter;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\StandardParameter;
use Modules\Masterdata\Equipment\Requests\StandardParameter\StoreStandardParameterRequest;

final class StoreStandardParameterAction
{
    use AsAction;

    public function asController(StoreStandardParameterRequest $request): JsonResponse
    {
        $parameter = StandardParameter::create($request->validated());

        return response()->json($parameter, 201);
    }
}
