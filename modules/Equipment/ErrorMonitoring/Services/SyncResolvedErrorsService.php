<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Services;

use InvalidArgumentException;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
use Modules\Masterdata\Equipment\Models\Equipment;

final class SyncResolvedErrorsService
{
    /**
     * Sync resolved error logs.
     *
     * @return int Number of synced logs
     *
     * @throws InvalidArgumentException
     */
    public function execute(?string $id = null): int
    {
        if ($id) {
            $log = EquipmentErrorLog::findOrFail($id);

            if (! $log->handled_at || ! $log->equipment_error_id) {
                throw new InvalidArgumentException('Error log is not resolved or has no associated error.');
            }

            $equipment = Equipment::findOrFail($log->equipment_id);
            $equipment->equipmentErrors()->detach($log->equipment_error_id);

            return 1;
        }

        $resolvedLogs = EquipmentErrorLog::query()
            ->whereNotNull('handled_at')
            ->whereNotNull('equipment_error_id')
            ->get(['equipment_id', 'equipment_error_id']);

        $synced = 0;
        foreach ($resolvedLogs as $log) {
            $equipment = Equipment::find($log->equipment_id);
            if ($equipment) {
                $equipment->equipmentErrors()->detach($log->equipment_error_id);
                $synced++;
            }
        }

        return $synced;
    }
}
