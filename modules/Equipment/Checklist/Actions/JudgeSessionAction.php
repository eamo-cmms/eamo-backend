<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\JudgeSessionRequest;
use Throwable;

final class JudgeSessionAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(JudgeSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sessionId = $data['session_id'];
        $currentUser = $request->user();

        $sessionTemplate = ChecklistSession::findOrFail($sessionId);
        $equipmentId = $sessionTemplate->equipment_id;
        $dateString = ! empty($data['timestamp'])
            ? Carbon::parse($data['timestamp'])->toDateString()
            : Carbon::today()->toDateString();

        $details = DB::transaction(function () use ($sessionId, $equipmentId, $dateString, $data, $currentUser) {
            $updated = [];
            foreach ($data['results'] as $item) {
                // Find or create template checklist detail item
                $detail = ChecklistDetail::firstOrCreate([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                ], [
                    'description' => $item['description'] ?? null,
                ]);

                // If description was updated in request, save it
                if (isset($item['description']) && $detail->description !== $item['description']) {
                    $detail->update(['description' => $item['description']]);
                }

                // Ad-hoc judging still creates a slot when no generated schedule exists.
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

        return response()->json([
            'message' => 'Checklist session judged successfully.',
            'details' => $details,
        ]);
    }
}
