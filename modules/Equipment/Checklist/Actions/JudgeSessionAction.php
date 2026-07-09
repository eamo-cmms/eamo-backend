<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
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

        $details = DB::transaction(function () use ($sessionId, $data) {
            $updated = [];
            foreach ($data['results'] as $item) {
                $updated[] = ChecklistDetail::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'checklist_id' => $item['checklist_id'],
                    ],
                    [
                        'result' => $item['result'],
                        'description' => $item['description'] ?? null,
                    ]
                );
            }

            return $updated;
        });

        return response()->json([
            'message' => 'Checklist session judged successfully.',
            'details' => $details,
        ]);
    }
}
