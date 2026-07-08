<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Unit;

final class IndexUnitAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $units = Unit::paginate($request->integer('per_page', 100));

        return response()->json($units);
    }
}
