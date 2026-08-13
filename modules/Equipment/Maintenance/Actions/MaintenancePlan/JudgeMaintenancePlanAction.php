<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenancePlan;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Requests\JudgeMaintenancePlanRequest;
use Modules\Equipment\Maintenance\Services\JudgeMaintenancePlanService;

final class JudgeMaintenancePlanAction
{
    use AsAction;

    public function __construct(
        private readonly JudgeMaintenancePlanService $judgeService
    ) {}

    public function asController(JudgeMaintenancePlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $currentUser = $request->user();

        $result = $this->judgeService->execute($data, $currentUser);

        return response()->json($result);
    }
}
