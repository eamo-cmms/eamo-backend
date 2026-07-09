<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class OverviewEquipmentParameterLogAction
{
    use AsAction;

    public function asController(Request $request, string $equipmentId): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
