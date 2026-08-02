<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\StoreIndividualChecklistScheduleRequest;

final class StoreIndividualChecklistScheduleAction
{
    use AsAction, SyncsUsersWithNotification;

    public function asController(string $sessionId, StoreIndividualChecklistScheduleRequest $request): JsonResponse
    {
        $session = ChecklistSession::findOrFail($sessionId);
        $validated = $request->validated();

        if ($session->schedules()->count() >= 100) {
            throw ValidationException::withMessages([
                'session' => ['Phiên checklist này đã đạt giới hạn tối đa 100 mốc lịch.'],
            ]);
        }

        $schedule = DB::transaction(function () use ($session, $validated) {
            $schedule = ChecklistSchedule::create([
                'checklist_session_id' => $session->id,
                'equipment_id' => $session->equipment_id,
                'checklist_detail_id' => $validated['checklist_detail_id'],
                'date' => $validated['date'],
                'original_date' => $validated['date'],
                'is_rescheduled' => false,
                'is_auto_generated' => false,
                'is_adhoc' => $validated['is_adhoc'] ?? true,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Tự động tạo 1 pending log tương ứng
            $schedule->logs()->create([
                'status' => 'pending',
                'result' => null,
            ]);

            if (! empty($validated['user_ids'])) {
                $schedule->users()->sync($validated['user_ids']);
                $label = ($session->name ?? 'Kiểm tra bổ sung')." ({$schedule->date})";
                $this->syncUsersAndNotify(
                    $schedule->users(),
                    $validated['user_ids'],
                    'checklist_session',
                    $schedule->id,
                    $label
                );
            }

            return $schedule;
        });

        return response()->json($schedule->load(['equipment', 'checklistDetail', 'logs', 'users']), 201);
    }
}
