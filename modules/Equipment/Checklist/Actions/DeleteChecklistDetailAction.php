<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistDetail;

final class DeleteChecklistDetailAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $detail = ChecklistDetail::findOrFail($id);
        $detail->delete();

        return response()->json([
            'message' => 'Checklist detail deleted successfully.',
        ]);
    }
}
