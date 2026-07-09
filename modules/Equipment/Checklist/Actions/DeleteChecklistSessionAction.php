<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Actions;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Checklist\Models\ChecklistSession;

final class DeleteChecklistSessionAction
{
    use AsAction;

    public function asController(string $id): JsonResponse
    {
        $session = ChecklistSession::findOrFail($id);
        $session->delete();

        return response()->json([
            'message' => 'Checklist session deleted successfully.',
        ]);
    }
}
