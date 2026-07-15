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
use Modules\Equipment\Checklist\Requests\StoreChecklistDetailRequest;
use Throwable;

final class StoreChecklistDetailAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(StoreChecklistDetailRequest $request): JsonResponse
    {
        $data = $request->validated();
        $currentUser = $request->user();

        $insertedDetails = DB::transaction(function () use ($data, $currentUser) {
            $sessionId = $data['session_id'] ?? null;
            $sessionDate = $data['session_date'] ?? now()->toDateString();
            $dateString = Carbon::parse($sessionDate)->toDateString();

            if (! $sessionId) {
                // Find or create session template for the equipment
                $sessionName = $data['session_name'] ?? ('Checklist - '.$data['equipment_id']);
                $session = ChecklistSession::firstOrCreate(
                    [
                        'equipment_id' => $data['equipment_id'],
                    ],
                    [
                        'name' => $sessionName,
                    ]
                );
                $sessionId = $session->id;
            } else {
                $session = ChecklistSession::findOrFail($sessionId);
            }

            $details = [];
            foreach ($data['checklists'] as $item) {
                // Create/get detail template item
                $detail = ChecklistDetail::firstOrCreate([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                ], [
                    'description' => $item['description'] ?? null,
                ]);

                // Create schedule record for this date
                $schedule = ChecklistSchedule::firstOrCreate([
                    'equipment_id' => $session->equipment_id,
                    'checklist_session_id' => $sessionId,
                    'checklist_detail_id' => $detail->id,
                    'date' => $dateString,
                ], [
                    'original_date' => $dateString,
                    'is_rescheduled' => false,
                ]);

                // Create log entry
                $log = $schedule->logs()->create([
                    'status' => 'completed',
                    'result' => $item['result'],
                    'checked_at' => Carbon::now(),
                ]);

                if ($currentUser) {
                    $log->users()->sync([$currentUser->id]);
                    $schedule->users()->sync([$currentUser->id]);
                }

                // Add relation so response formats correctly
                $detail->setRelation('logs', collect([$log]));
                $details[] = $detail;
            }

            return $details;
        });

        return response()->json($insertedDetails, 201);
    }
}
