<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Requests\ShowChecklistSessionRequest;
use Modules\Equipment\Checklist\Services\ShowChecklistSessionService;

final class ShowChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly ShowChecklistSessionService $service
    ) {}

    public function asController(string $id, ShowChecklistSessionRequest $request): JsonResponse
    {
        $session = $this->service->execute($id, $request->all());

        return response()->json($session);
    }
}
