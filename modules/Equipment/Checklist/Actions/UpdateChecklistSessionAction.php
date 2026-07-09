<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Requests\UpdateChecklistSessionRequest;
use Throwable;

final class UpdateChecklistSessionAction
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateChecklistSessionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $session = DB::transaction(function () use ($id, $data) {
            $session = ChecklistSession::findOrFail($id);

            $sessionData = array_diff_key($data, array_flip(['user_ids']));
            $session->update($sessionData);

            if (array_key_exists('user_ids', $data)) {
                $session->users()->sync($data['user_ids'] ?? []);
            }

            return $session;
        });

        return response()->json($session->load('users'));
    }
}
