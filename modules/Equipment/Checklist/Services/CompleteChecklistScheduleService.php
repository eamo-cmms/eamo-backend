<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Models\User;
use Carbon\Carbon;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;

final class CompleteChecklistScheduleService
{
    /**
     * @return array{message: string, schedule_id: string, is_completed: true}
     */
    public function execute(string $id, ?User $currentUser): array
    {
        $schedule = ChecklistSchedule::findOrFail($id);

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

        if ($currentUser) {
            $log->users()->sync([$currentUser->id]);
        }

        return [
            'message' => 'Checklist schedule marked completed successfully.',
            'schedule_id' => $schedule->id,
            'is_completed' => true,
        ];
    }
}
