<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\ErrorMonitoring\Requests\StoreOperatingTimeRequest;

final class StoreOperatingTimeAction
{
    use AsAction;

    /**
     * Create operating time record and calculate metrics.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): OperatingTime
    {
        $time = new OperatingTime($data);
        $time->date = now()->toDateString();
        $time->calculateMetrics()->save();

        return $time->load('equipment');
    }

    public function asController(StoreOperatingTimeRequest $request): JsonResponse
    {
        $time = $this->handle($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $time,
        ], 201);
    }
}
