<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Services\DeleteChecklistSessionService;

final class DeleteChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteChecklistSessionService $service
    ) {}

    public function asController(string $id): JsonResponse
    {
        $result = $this->service->execute($id);

        return response()->json($result);
    }
}
