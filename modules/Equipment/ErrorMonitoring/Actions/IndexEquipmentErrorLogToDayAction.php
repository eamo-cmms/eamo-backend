<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class IndexEquipmentErrorLogToDayAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
