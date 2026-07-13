<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Services;

use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

final class StoreEquipmentErrorLogService
{
    /**
     * Store a new equipment error log and sync handlers.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): EquipmentErrorLog
    {
        $handlerIds = $data['handler_ids'] ?? [];
        unset($data['handler_ids']);

        $log = EquipmentErrorLog::create($data);
        $log->handlers()->sync($handlerIds);

        return $log->load(['equipment', 'equipmentError', 'handlers']);
    }
}
