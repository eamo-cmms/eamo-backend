<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\CreateDailySessionRequest;
use Modules\Equipment\Checklist\Services\CreateDailySessionService;

final class CreateDailySessionAction
{
    use AsAction;

    public function __construct(
        private readonly CreateDailySessionService $service
    ) {}

    public function asController(CreateDailySessionRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated(), $request->user());

        return response()->json($result['data'], $result['status']);
    }
}
