<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Actions\Equipment;

use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Actions\MaintenanceLog\StoreMaintenanceLogAction;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Requests\Equipment\MarkLastMaintenanceRequest;

final class MarkLastMaintenanceAction
{
    use AsAction;

    /**
     * Manually mark the last maintenance datetime for an equipment.
     * This invokes StoreMaintenanceLogAction to record a maintenance log and update equipment last_maintenance.
     *
     * URL parameter:
     *   id: string (UUID, required)
     *
     * Request body (MarkLastMaintenanceRequest):
     *   datetime:     string (required, ISO-8601 datetime e.g. "2026-08-11 09:00:00")
     *   note:         string|null (optional, reason / description)
     *   result:       string|null (optional, default 'Completed')
     *   type:         string|null (optional, default 'periodic')
     */
    public function asController(string $id, MarkLastMaintenanceRequest $request): JsonResponse
    {
        $equipment = Equipment::findOrFail($id);

        $datetime = $request->input('datetime') ?? now()->toIso8601String();
        $user = $request->user();
        $note = $request->input('note') ?? 'Đánh dấu bảo trì thiết bị';
        $type = $request->input('type') ?? 'periodic';

        // Gọi action StoreMaintenanceLogAction để lưu log và cập nhật last_maintenance
        $log = StoreMaintenanceLogAction::run([
            'equipment_id' => $equipment->id,
            'user_id'      => $user?->id,
            'log_date'     => $datetime,
            'type'         => $type,
            'note'         => $note,
        ], $user);

        $equipment->refresh();

        return response()->json([
            'status'           => 'success',
            'message'          => 'Last maintenance datetime updated and log recorded successfully.',
            'equipment_id'     => $equipment->id,
            'last_maintenance' => $equipment->last_maintenance,
            'log'              => $log,
        ]);
    }
}
