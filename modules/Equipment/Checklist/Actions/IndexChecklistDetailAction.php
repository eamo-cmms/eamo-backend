<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;

final class IndexChecklistDetailAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $filters = $request->all();
        $details = ChecklistDetail::query()
            ->filter($filters)
            ->paginate($request->integer('per_page', 15));

        return response()->json($details);
    }
}
