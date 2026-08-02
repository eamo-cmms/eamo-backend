<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistDetail;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\UpdateChecklistDetailRequest;
use Modules\Equipment\Checklist\Services\UpdateChecklistDetailService;
use Throwable;

final class UpdateChecklistDetailAction
{
    use AsAction;

    public function __construct(
        private readonly UpdateChecklistDetailService $service
    ) {}

    /**
     * @throws Throwable
     */
    public function asController(UpdateChecklistDetailRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated(), $request->user());

        return response()->json($result);
    }
}
