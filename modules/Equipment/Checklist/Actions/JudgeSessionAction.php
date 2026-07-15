<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistLog;
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

        $details = DB::transaction(function () use ($sessionId, $data, $currentUser) {
            $updated = [];
            foreach ($data['results'] as $item) {
                $detail = ChecklistDetail::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'checklist_id' => $item['checklist_id'],
                    ],
                    [
                        'description' => $item['description'] ?? null,
                    ]
                );

                $log = new ChecklistLog([
                    'result' => $item['result'],
                ]);

                if (! empty($data['timestamp'])) {
                    $log->created_at = $data['timestamp'];
                    $log->updated_at = $data['timestamp'];
                }

                $detail->logs()->save($log);

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
