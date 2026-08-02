<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\ShowDailySessionRequest;
use Modules\Equipment\Checklist\Services\ShowDailySessionService;

final class ShowDailySessionAction
{
    use AsAction;

    public function __construct(
        private readonly ShowDailySessionService $service
    ) {}

    public function asController(ShowDailySessionRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated());

        return response()->json($result['data'], $result['status']);
    }
}
