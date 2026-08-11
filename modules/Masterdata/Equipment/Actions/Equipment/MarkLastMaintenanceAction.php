<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\MarkLastMaintenanceRequest;

final class MarkLastMaintenanceAction
{
    use AsAction;

    /**
     * Manually mark the last maintenance datetime for an equipment.
     * This resets the operating-time accumulation cycle for maintenance tracking.
     *
     * URL parameter:
     *   id: string (UUID, required)
     *
     * Request body (MarkLastMaintenanceRequest):
     *   datetime:     string (required, ISO-8601 datetime e.g. "2026-08-11 09:00:00")
     *   note:         string|null (optional, reason / description)
     */
    public function asController(string $id, MarkLastMaintenanceRequest $request): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $equipment->update([
            'last_maintenance' => [
                'equipment_id' => $equipment->id,
                'datetime'     => $request->input('datetime') ?? now()->toDateTimeString(),
                'user_id'      => $request->user()?->id,
            ],
        ]);

        $equipment->refresh();

        return response()->json([
            'status'           => 'success',
            'message'          => 'Last maintenance datetime updated successfully.',
            'equipment_id'     => $equipment->id,
            'last_maintenance' => $equipment->last_maintenance,
        ]);
    }
}
