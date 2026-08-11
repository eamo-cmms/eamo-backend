<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions\ChecklistDetail;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Requests\IndexChecklistDetailRequest;

final class IndexChecklistDetailAction
{
    use AsAction;

    public function asController(IndexChecklistDetailRequest $request): JsonResponse
    {
        $filters = $request->all();
        $perPage = (int) ($filters['per_page'] ?? 15);

        $details = ChecklistDetail::query()
            ->filter($filters)
            ->paginate($perPage);

        return response()->json($details);
    }
}
