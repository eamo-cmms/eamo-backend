<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Services\EquipmentCascadeSoftDeleteService;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class DeleteChecklistSessionAction
{
    use AsAction;

    public function asController(string $id, EquipmentCascadeSoftDeleteService $cascadeService): JsonResponse
    {
        $session = ChecklistSession::findOrFail($id);
        $cascadeService->deleteChecklistSession($session);

        return response()->json([
            'message' => 'Checklist session deleted successfully.',
        ]);
    }
}
