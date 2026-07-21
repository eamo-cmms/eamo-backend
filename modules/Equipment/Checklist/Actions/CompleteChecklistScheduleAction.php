<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;

final class CompleteChecklistScheduleAction
{
    use AsAction;

    public function asController(Request $request, string $id): JsonResponse
    {
        $schedule = ChecklistSchedule::findOrFail($id);
        $user = $request->user();

        $log = $schedule->logs()->where('status', 'pending')->first();
        if (! $log) {
            $log = $schedule->logs()->latest('checked_at')->first();
        }
        if (! $log) {
            $log = $schedule->logs()->create([
                'status' => 'pending',
                'result' => null,
            ]);
        }

        $log->update([
            'status' => 'completed',
            'result' => 'pass',
            'checked_at' => Carbon::now(),
        ]);

        if ($user) {
            $log->users()->sync([$user->id]);
        }

        return response()->json([
            'message' => 'Checklist schedule marked completed successfully.',
            'schedule_id' => $schedule->id,
            'is_completed' => true,
        ]);
    }
}
