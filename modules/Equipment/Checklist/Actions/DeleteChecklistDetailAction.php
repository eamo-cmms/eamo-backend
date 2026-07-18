<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Equipment\Checklist\Models\ChecklistDetail;

final class DeleteChecklistDetailAction
{
    use AsAction;

    public function asController(string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $detail = ChecklistDetail::findOrFail($id);
        $cascadeService->deleteChecklistDetail($detail);

        return response()->json([
            'message' => 'Checklist detail deleted successfully.',
        ]);
    }
}
