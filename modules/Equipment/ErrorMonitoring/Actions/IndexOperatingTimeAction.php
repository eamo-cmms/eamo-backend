<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;

final class IndexOperatingTimeAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $times = OperatingTime::with('equipment')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $times,
        ]);
    }
}
