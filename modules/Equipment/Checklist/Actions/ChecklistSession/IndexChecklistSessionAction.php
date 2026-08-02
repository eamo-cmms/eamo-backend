<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\IndexChecklistSessionRequest;
use Modules\Equipment\Checklist\Services\IndexChecklistSessionService;

final class IndexChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly IndexChecklistSessionService $service
    ) {}

    public function asController(IndexChecklistSessionRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated());

        return response()->json($result);
    }
}
