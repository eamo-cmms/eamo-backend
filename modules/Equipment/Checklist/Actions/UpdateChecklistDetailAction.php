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
        $currentUser = $request->user();

        $updatedDetails = DB::transaction(function () use ($sessionId, $data, $currentUser) {
            $details = [];
            foreach ($data['checklists'] as $item) {
                $detail = ChecklistDetail::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'checklist_id' => $item['checklist_id'],
                    ],
                    [
                        'description' => $item['description'] ?? null,
                    ]
                );

                $log = $detail->logs()->create([
                    'result' => $item['result'],
                ]);

                if ($currentUser) {
                    $log->users()->sync([$currentUser->id]);
                }

                $details[] = $detail;
            }

            return $details;
        });

        return response()->json($updatedDetails);
    }
}
