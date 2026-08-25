<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSchedule;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Services\CompleteChecklistScheduleService;

final class CompleteChecklistScheduleAction
{
    use AsAction;

    public function __construct(
        private readonly CompleteChecklistScheduleService $service
    ) {}

    public function asController(Request $request, string $id): JsonResponse
    {
        $schedule = ChecklistSchedule::findOrFail($id);
        Gate::authorize('complete', $schedule);

        $result = $this->service->execute($id, $request->user());

        return response()->json($result);
    }
}
