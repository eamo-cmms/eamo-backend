<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Unit;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Unit;
use Modules\Masterdata\Equipment\Requests\Unit\StoreUnitRequest;

final class StoreUnitAction
{
    use AsAction;

    public function asController(StoreUnitRequest $request): JsonResponse
    {
        $unit = Unit::create($request->validated());

        return response()->json($unit, 201);
    }
}
