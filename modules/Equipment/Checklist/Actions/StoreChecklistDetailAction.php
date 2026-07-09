<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
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

        $insertedDetails = DB::transaction(function () use ($data, $request) {
            $sessionId = $data['session_id'] ?? null;

            if (! $sessionId) {
                // Find or create session for the equipment on the specified date
                $sessionDate = $data['session_date'] ?? now()->toDateTimeString();
                $sessionName = $data['session_name'] ?? ('Checklist Session - '.now()->toDateTimeString());
                $session = ChecklistSession::firstOrCreate(
                    [
                        'equipment_id' => $data['equipment_id'],
                        'session_date' => $sessionDate,
                    ],
                    [
                        'name' => $sessionName,
                        'created_by' => $request->user()?->id ?? 'system',
                    ]
                );
                $sessionId = $session->id;
            }

            $details = [];
            foreach ($data['checklists'] as $item) {
                $details[] = ChecklistDetail::create([
                    'session_id' => $sessionId,
                    'checklist_id' => $item['checklist_id'],
                    'result' => $item['result'],
                    'description' => $item['description'] ?? null,
                ]);
            }

            return $details;
        });

        return response()->json($insertedDetails, 201);
    }
}
