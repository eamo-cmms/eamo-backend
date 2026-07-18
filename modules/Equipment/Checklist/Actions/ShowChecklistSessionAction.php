<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class ShowChecklistSessionAction
{
    use AsAction;

    public function asController(string $id, Request $request): JsonResponse
    {
        $relations = ['users'];
        if (filter_var($request->input('with_details', $request->input('include_details', false)), FILTER_VALIDATE_BOOLEAN)) {
            $relations[] = 'details';
        }
        $query = ChecklistSession::query()->with($relations);
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        } elseif ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $session = $query->findOrFail($id);

        return response()->json($session);
    }
}
