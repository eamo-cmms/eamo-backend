<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\GetEquipmentChecklistStatusRequest;
use Modules\Equipment\Checklist\Services\GetEquipmentChecklistStatusService;

final class GetEquipmentChecklistStatusAction
{
    use AsAction;

    public function __construct(
        private readonly GetEquipmentChecklistStatusService $service
    ) {}

    public function asController(GetEquipmentChecklistStatusRequest $request): JsonResponse
    {
        $result = $this->service->execute($request->validated());

        return response()->json($result);
    }
}
