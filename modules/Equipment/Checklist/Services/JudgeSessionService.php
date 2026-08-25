<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Throwable;

final class JudgeSessionService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, details: array<int, ChecklistDetail>}
     *
     * @throws Throwable
     */
    public function execute(array $data, ?User $currentUser): array
    {
        $sessionId = $data['session_id'];

        $sessionTemplate = ChecklistSession::findOrFail($sessionId);
        $equipmentId = $sessionTemplate->equipment_id;
        $dateString = ! empty($data['timestamp'])
            ? Carbon::parse($data['timestamp'])->toDateString()
            : Carbon::today()->toDateString();

        $details = DB::transaction(function () use ($sessionId, $equipmentId, $dateString, $data, $currentUser): array {
            $updated = [];
            foreach ($data['results'] as $item) {
                $detail = ChecklistDetail::firstOrCreate([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                ], [
                    'description' => $item['description'] ?? null,
                ]);

                if (isset($item['description']) && $detail->description !== $item['description']) {
                    $detail->update(['description' => $item['description']]);
                }

                $schedule = ChecklistSchedule::firstOrCreate([
                    'equipment_id' => $equipmentId,
                    'checklist_session_id' => $sessionId,
                    'checklist_detail_id' => $detail->id,
                    'date' => $dateString,
                ], [
                    'original_date' => $dateString,
                    'is_rescheduled' => false,
                ]);

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

                $checkedAt = ! empty($data['timestamp'])
                    ? Carbon::parse($data['timestamp'])
                    : Carbon::now();

                $log->update([
                    'status' => 'completed',
                    'result' => $item['result'],
                    'checked_at' => $checkedAt,
                ]);

                $targetUserIds = $data['user_ids'] ?? ($currentUser ? [$currentUser->id] : []);
                if (! empty($targetUserIds)) {
                    $log->users()->sync($targetUserIds);
                }

                $updated[] = $detail;
            }

            return $updated;
        });

        return [
            'message' => __('checklist.session_judged'),
            'details' => $details,
        ];
    }
}
