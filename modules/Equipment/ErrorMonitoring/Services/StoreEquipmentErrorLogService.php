<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Services;

use App\Concerns\SyncsUsersWithNotification;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

final class StoreEquipmentErrorLogService
{
    use SyncsUsersWithNotification;

    /**
     * Store a new equipment error log and sync handlers.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): EquipmentErrorLog
    {
        $handlerIds = $data['handler_ids'] ?? [];
        unset($data['handler_ids']);

        $currentUserId = auth('api')->id() ?? auth()->id();

        if (empty($data['occurred_at'])) {
            $data['occurred_at'] = now();
        }

        if (empty($data['handler_id']) && $currentUserId) {
            $data['handler_id'] = $currentUserId;
        }

        if (empty($handlerIds) && $currentUserId) {
            $handlerIds = [$currentUserId];
        }

        $log = EquipmentErrorLog::create($data);

        $log->loadMissing(['equipment', 'equipmentError']);
        $label = ($log->equipment?->name ?? 'Equipment').' - '.($log->equipmentError?->name ?? 'Error');

        $this->syncUsersAndNotify(
            $log->handlers(),
            $handlerIds,
            'error_log',
            $log->id,
            $label
        );

        return $log->load(['equipment', 'equipmentError', 'handlers']);
    }
}
