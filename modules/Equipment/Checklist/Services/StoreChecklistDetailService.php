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

final class StoreChecklistDetailService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<int, ChecklistDetail>
     *
     * @throws Throwable
     */
    public function execute(array $data, ?User $currentUser): array
    {
        return DB::transaction(function () use ($data, $currentUser): array {
            $sessionId = $data['session_id'];
            $sessionDate = $data['session_date'] ?? now()->toDateString();
            $dateString = Carbon::parse($sessionDate)->toDateString();

            $session = ChecklistSession::findOrFail($sessionId);

            $details = [];
            foreach ($data['checklists'] as $item) {
                $detail = ChecklistDetail::firstOrCreate([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                ], [
                    'description' => $item['description'] ?? null,
                ]);

                $schedule = ChecklistSchedule::firstOrCreate([
                    'equipment_id' => $session->equipment_id,
                    'checklist_session_id' => $sessionId,
                    'checklist_detail_id' => $detail->id,
                    'date' => $dateString,
                ], [
                    'original_date' => $dateString,
                    'is_rescheduled' => false,
                ]);

                $log = $schedule->logs()->create([
                    'status' => 'completed',
                    'result' => $item['result'],
                    'checked_at' => Carbon::now(),
                ]);

                if ($currentUser) {
                    $log->users()->sync([$currentUser->id]);
                    $schedule->users()->sync([$currentUser->id]);
                }

                $detail->setRelation('logs', collect([$log]));
                $details[] = $detail;
            }

            return $details;
        });
    }
}
