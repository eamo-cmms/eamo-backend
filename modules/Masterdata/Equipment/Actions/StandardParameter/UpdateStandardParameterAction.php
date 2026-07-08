<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\StandardParameter;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\StandardParameter;
use Modules\Masterdata\Equipment\Requests\StandardParameter\UpdateStandardParameterRequest;

final class UpdateStandardParameterAction
{
    use AsAction;

    public function asController(UpdateStandardParameterRequest $request, string $id): JsonResponse
    {
        $parameter = StandardParameter::findOrFail($id);
        $parameter->update($request->validated());

        return response()->json($parameter);
    }
}
