<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistDetail;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Services\DeleteChecklistDetailService;

final class DeleteChecklistDetailAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteChecklistDetailService $service
    ) {}

    public function asController(string $id): JsonResponse
    {
        $result = $this->service->execute($id);

        return response()->json($result);
    }
}
