<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\JudgeSessionRequest;
use Modules\Equipment\Checklist\Services\JudgeSessionService;
use Throwable;

final class JudgeSessionAction
{
    use AsAction;

    public function __construct(
        private readonly JudgeSessionService $service
    ) {}

    /**
     * @throws Throwable
     */
    public function asController(JudgeSessionRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated(), $request->user());

        return response()->json($result);
    }
}
