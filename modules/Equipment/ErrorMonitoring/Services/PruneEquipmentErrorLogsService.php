<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

final class PruneEquipmentErrorLogsService
{
    /**
     * Prune excess error log records for each equipment exceeding the limit.
     */
    public function execute(?int $limit = null): void
    {
        $limit = $limit ?? EquipmentErrorLog::MAX_LOG_RECORDS;

        $equipmentIds = EquipmentErrorLog::query()
            ->select('equipment_id')
            ->groupBy('equipment_id')
            ->havingRaw('COUNT(*) > ?', [$limit])
            ->pluck('equipment_id');

        foreach ($equipmentIds as $equipmentId) {
            $cutoff = EquipmentErrorLog::query()
                ->where('equipment_id', $equipmentId)
                ->select(['occurred_at', 'id'])
                ->orderBy('occurred_at', 'desc')
                ->orderBy('id', 'desc')
                ->skip($limit - 1)
                ->first();

            if (! $cutoff) {
                continue;
            }

            $cutoffOccurredAt = $cutoff->occurred_at;
            $cutoffId = $cutoff->id;

            EquipmentErrorLog::query()
                ->where('equipment_id', $equipmentId)
                ->where(function (Builder $query) use ($cutoffOccurredAt, $cutoffId): void {
                    $query
                        ->where('occurred_at', '<', $cutoffOccurredAt)
                        ->orWhere(function (Builder $query) use ($cutoffOccurredAt, $cutoffId): void {
                            $query
                                ->where('occurred_at', $cutoffOccurredAt)
                                ->where('id', '<', $cutoffId);
                        });
                })
                ->delete();
        }
    }
}
