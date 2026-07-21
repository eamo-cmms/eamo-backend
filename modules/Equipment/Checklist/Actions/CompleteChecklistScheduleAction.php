<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Services\CompleteChecklistScheduleService;

final class CompleteChecklistScheduleAction
{
    use AsAction;

    public function __construct(
        private readonly CompleteChecklistScheduleService $service
    ) {}

    public function asController(Request $request, string $id): JsonResponse
    {
        $result = $this->service->execute($id, $request->user());

        return response()->json($result);
    }
}
