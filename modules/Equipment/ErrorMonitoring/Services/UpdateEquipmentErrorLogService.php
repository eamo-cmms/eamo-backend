<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Services;

use App\Concerns\SyncsUsersWithNotification;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

final class UpdateEquipmentErrorLogService
{
    use SyncsUsersWithNotification;

    /**
     * Update an equipment error log and sync handlers.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(EquipmentErrorLog $log, array $data): EquipmentErrorLog
    {
        if (array_key_exists('handler_ids', $data)) {
            $handlerIds = $data['handler_ids'] ?? [];
            $log->loadMissing(['equipment', 'equipmentError']);
            $label = ($log->equipment?->name ?? 'Equipment').' - '.($log->equipmentError?->name ?? 'Error');
            $this->syncUsersAndNotify(
                $log->handlers(),
                $handlerIds,
                'error_log',
                $log->id,
                $label
            );
            unset($data['handler_ids']);
        }

        $log->update($data);

        return $log->load(['equipment', 'equipmentError', 'handlers']);
    }
}
