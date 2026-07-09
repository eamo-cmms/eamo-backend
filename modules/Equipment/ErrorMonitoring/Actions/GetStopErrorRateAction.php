<?php

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

final readonly class GetStopErrorRateAction
{
    use AsAction;

    public function asController(): JsonResponse
    {
        // TODO: Implement custom logic
        return response()->json([]);
    }
}
