<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Modules\Masterdata\Equipment\Models\Equipment;

final class SyncEquipmentErrorsService
{
    /**
     * Sync error IDs to the equipment.
     *
     * @param  Equipment  $equipment
     * @param  array<int, string>  $errorIds
     */
    public function sync(Equipment $equipment, array $errorIds): void
    {
        $equipment->equipmentErrors()->sync($errorIds);
    }
}
