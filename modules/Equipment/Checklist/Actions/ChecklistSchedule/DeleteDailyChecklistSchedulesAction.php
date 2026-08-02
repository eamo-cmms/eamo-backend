<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSchedule;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\DeleteDailyChecklistSchedulesRequest;
use Modules\Equipment\Checklist\Services\DeleteDailyChecklistSchedulesService;

final class DeleteDailyChecklistSchedulesAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteDailyChecklistSchedulesService $service
    ) {}

    public function asController(DeleteDailyChecklistSchedulesRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated());

        return response()->json($result);
    }
}
