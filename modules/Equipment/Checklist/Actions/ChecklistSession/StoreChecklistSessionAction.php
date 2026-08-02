<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\StoreChecklistSessionRequest;
use Modules\Equipment\Checklist\Services\StoreChecklistSessionService;
use Throwable;

final class StoreChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly StoreChecklistSessionService $storeChecklistSessionService
    ) {}

    /**
     * @throws Throwable
     */
    public function asController(StoreChecklistSessionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? ($request->user()?->id ? [$request->user()->id] : []);

        $sessionResponse = $this->storeChecklistSessionService->execute($data, $userIds);

        return response()->json($sessionResponse, 201);
    }
}
