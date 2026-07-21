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

final class UpdateChecklistDetailService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<int, ChecklistDetail>
     *
     * @throws Throwable
     */
    public function execute(array $data, ?User $currentUser): array
    {
        $sessionId = $data['session_id'];

        $session = ChecklistSession::findOrFail($sessionId);
        $equipmentId = $session->equipment_id;

        $dateString = ! empty($data['date'])
            ? Carbon::parse($data['date'])->toDateString()
            : (ChecklistSchedule::where('checklist_session_id', $sessionId)->latest('date')->value('date') ?? Carbon::today()->toDateString());

        return DB::transaction(function () use ($sessionId, $equipmentId, $dateString, $data, $currentUser): array {
            $details = [];
            foreach ($data['checklists'] as $item) {
                $detail = ChecklistDetail::firstOrCreate([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                ], [
                    'description' => $item['description'] ?? null,
                ]);

                if (isset($item['description']) && $detail->description !== $item['description']) {
                    $detail->update(['description' => $item['description']]);
                }

                if (array_key_exists('result', $item) && $item['result'] !== null) {
                    $schedule = ChecklistSchedule::firstOrCreate([
                        'equipment_id' => $equipmentId,
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
                }

                $details[] = $detail;
            }

            return $details;
        });
    }
}
