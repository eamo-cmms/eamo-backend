<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistSession;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\Checklist\Services\DeleteChecklistSessionService;

final class DeleteChecklistSessionAction
{
    use AsAction;

    public function __construct(
        private readonly DeleteChecklistSessionService $service
    ) {}

    public function asController(string $id): JsonResponse
    {
        $session = ChecklistSession::findOrFail($id);
        Gate::authorize('delete', $session);

        $result = $this->service->execute($id);

        return response()->json($result);
    }
}
