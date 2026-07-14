<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use App\Concerns\SyncsUsersWithNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\StoreChecklistSessionRequest;
use Throwable;

final class StoreChecklistSessionAction
{
    use AsAction, SyncsUsersWithNotification;

    /**
     * @throws Throwable
     */
    public function asController(StoreChecklistSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? ($request->user()?->id ? [$request->user()->id] : []);

        $session = DB::transaction(function () use ($data, $userIds) {
            $session = ChecklistSession::create([
                'name' => $data['name'],
                'equipment_id' => $data['equipment_id'],
                'session_date' => $data['session_date'],
            ]);

            if (! empty($userIds)) {
                $this->syncUsersAndNotify(
                    $session->users(),
                    $userIds,
                    'checklist_session',
                    $session->id,
                    $session->name
                );
            }

            if (! empty($data['details'])) {
                foreach ($data['details'] as $detail) {
                    $session->details()->create([
                        'checklist_id' => $detail['checklist_id'],
                        'result' => $detail['result'],
                        'description' => $detail['description'] ?? null,
                    ]);
                }
            }

            return $session;
        });

        return response()->json($session->load(['details', 'users']), 201);
    }
}
