<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;
use Modules\Equipment\ErrorMonitoring\Requests\UpdateOperatingTimeRequest;

final class UpdateOperatingTimeAction
{
    use AsAction;

    public function asController(string $id, UpdateOperatingTimeRequest $request): JsonResponse
    {
        $time = OperatingTime::findOrFail($id);

        $data = $request->validated();

        $startTime = Carbon::parse($data['start_time'] ?? $time->start_time);
        $endTime = Carbon::parse($data['end_time'] ?? $time->end_time);

        // Calculate working_time in hours (floating point)
        $diffInMinutes = $startTime->diffInMinutes($endTime);
        $workingTime = round($diffInMinutes / 60.0, 2);

        $plannedStopTime = (float) ($data['planned_stop_time'] ?? $time->planned_stop_time);
        $unplannedStopTime = (float) ($data['unplanned_stop_time'] ?? $time->unplanned_stop_time ?? 0);

        $plannedOperatingTime = max(0.0, $workingTime - $plannedStopTime);
        $actualOperatingTime = max(0.0, $plannedOperatingTime - $unplannedStopTime);

        $availabilityFactor = $plannedOperatingTime > 0
            ? round(($actualOperatingTime / $plannedOperatingTime) * 100, 2)
            : 0.0;

        $data['working_time'] = $workingTime;
        $data['planned_operating_time'] = $plannedOperatingTime;
        $data['actual_operating_time'] = $actualOperatingTime;
        $data['availability_factor'] = $availabilityFactor;

        $time->update($data);
        $time->load('equipment');

        return response()->json([
            'status' => 'success',
            'data' => $time,
        ]);
    }
}
