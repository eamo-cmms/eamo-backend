<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\ErrorMonitoring\Requests\UpdateOperatingTimeRequest;

final class UpdateOperatingTimeAction
{
    use AsAction;

    /**
     * Update operating time record and recalculate metrics.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(OperatingTime $time, array $data): OperatingTime
    {
        $time->fill($data)
            ->calculateMetrics()
            ->save();

        return $time->load('equipment');
    }

    public function asController(string $id, UpdateOperatingTimeRequest $request): JsonResponse
    {
        $time = OperatingTime::findOrFail($id);

        $this->handle($time, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $time,
        ]);
    }
}
