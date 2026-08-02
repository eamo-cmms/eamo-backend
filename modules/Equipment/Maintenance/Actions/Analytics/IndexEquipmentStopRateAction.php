<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\Analytics;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class IndexEquipmentStopRateAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
