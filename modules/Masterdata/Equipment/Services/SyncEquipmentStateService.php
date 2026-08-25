<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Modules\Masterdata\Equipment\Models\Equipment;

final class SyncEquipmentStateService
{
    /**
     * Create initial equipment state.
     */
    public function create(Equipment $equipment, string $state): void
    {
        $equipment->equipmentState()->create([
            'state' => $state,
        ]);
    }

    /**
     * Set or update equipment state.
     */
    public function set(Equipment $equipment, string $state): void
    {
        $equipment->equipmentState()->updateOrCreate(
            ['equipment_id' => $equipment->id],
            ['state' => $state]
        );
    }
}
