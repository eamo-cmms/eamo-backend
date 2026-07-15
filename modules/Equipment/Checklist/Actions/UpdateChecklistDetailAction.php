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
use Modules\Equipment\Checklist\Requests\UpdateChecklistDetailRequest;
use Throwable;

final class UpdateChecklistDetailAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(UpdateChecklistDetailRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sessionId = $data['session_id'];
        $currentUser = $request->user();

        $session = ChecklistSession::findOrFail($sessionId);
        $equipmentId = $session->equipment_id;

        $dateString = ! empty($data['date'])
            ? Carbon::parse($data['date'])->toDateString()
            : (ChecklistSchedule::where('checklist_session_id', $sessionId)->latest('date')->value('date') ?? Carbon::today()->toDateString());

        $updatedDetails = DB::transaction(function () use ($sessionId, $equipmentId, $dateString, $data, $currentUser) {
            $details = [];
            foreach ($data['checklists'] as $item) {
                // Find or create template detail item
                $detail = ChecklistDetail::firstOrCreate([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                ], [
                    'description' => $item['description'] ?? null,
                ]);

                // If description is updated, update it
                if (isset($item['description']) && $detail->description !== $item['description']) {
                    $detail->update(['description' => $item['description']]);
                }

                // A description-only update must not create a new schedule or log.
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

        return response()->json($updatedDetails);
    }
}
