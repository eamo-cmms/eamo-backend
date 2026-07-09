<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
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

        $updatedDetails = DB::transaction(function () use ($sessionId, $data) {
            $details = [];
            foreach ($data['checklists'] as $item) {
                $details[] = ChecklistDetail::updateOrCreate(
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

            return $details;
        });

        return response()->json($updatedDetails);
    }
}
