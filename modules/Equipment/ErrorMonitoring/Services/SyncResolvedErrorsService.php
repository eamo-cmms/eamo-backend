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

            if ($log->is_synced) {
                throw new InvalidArgumentException('This error log has already been synced.');
            }

            if (! $log->handled_at || ! $log->equipment_error_id) {
                throw new InvalidArgumentException('Error log is not resolved or has no associated error.');
            }

            $log->update(['is_synced' => true]);

            return 1;
        }

        $resolvedLogs = EquipmentErrorLog::query()
            ->whereNotNull('handled_at')
            ->whereNotNull('equipment_error_id')
            ->where('is_synced', false)
            ->get();

        $synced = 0;
        foreach ($resolvedLogs as $log) {
            $log->update(['is_synced' => true]);
            $synced++;
        }

        return $synced;
    }
}
