<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Unit;

final class ShowUnitAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $unit = Unit::findOrFail($id);

        return response()->json($unit);
    }
}
