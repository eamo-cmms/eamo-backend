<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\UpdateChecklistSessionRequest;
use Modules\Equipment\Checklist\Services\ChecklistSessionUpdateService;
use Throwable;

final class UpdateChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly ChecklistSessionUpdateService $updateService
    ) {}

    /**
     * @throws Throwable
     */
    public function asController(string $id, UpdateChecklistSessionRequest $request): JsonResponse
    {
        $sessionResponse = $this->updateService->execute($id, $request->validated());

        return response()->json($sessionResponse);
    }
}
