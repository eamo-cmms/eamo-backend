<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Unit;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Unit;
use Modules\Masterdata\Equipment\Requests\Unit\UpdateUnitRequest;

final class UpdateUnitAction
{
    use AsAction;

    public function asController(UpdateUnitRequest $request, string $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);
        $unit->update($request->validated());

        return response()->json($unit);
    }
}
