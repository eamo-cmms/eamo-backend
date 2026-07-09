<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class IndexChecklistSessionAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $sessions = ChecklistSession::query()
            ->with(['equipment', 'details', 'users'])
            ->filter($request->all())
            ->paginate($request->integer('per_page', 15));

        return response()->json($sessions);
    }
}
