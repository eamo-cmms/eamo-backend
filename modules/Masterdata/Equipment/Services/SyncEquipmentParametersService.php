<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Services;

use Modules\Masterdata\Equipment\Models\Equipment;

final class SyncEquipmentParametersService
{
    /**
     * Create parameters for a newly created equipment.
     *
     * @param  Equipment  $equipment
     * @param  array<int, array<string, mixed>>  $parameters
     */
    public function create(Equipment $equipment, array $parameters): void
    {
        foreach ($parameters as $param) {
            if (! empty($param['code']) && ! empty($param['name'])) {
                $equipment->equipmentParameters()->create([
                    'code' => $param['code'],
                    'name' => $param['name'],
                    'unit_id' => $param['unit_id'] ?? null,
                    'equipment_category_id' => $equipment->equipment_category_id,
                ]);
            }
        }
    }

    /**
     * Sync parameters for an existing equipment (update existing, create new, delete omitted).
     *
     * @param  Equipment  $equipment
     * @param  array<int, array<string, mixed>>  $parameters
     */
    public function sync(Equipment $equipment, array $parameters): void
    {
        $keepIds = [];
        foreach ($parameters as $param) {
            if (! empty($param['code']) && ! empty($param['name'])) {
                $record = $equipment->equipmentParameters()->updateOrCreate(
                    ['id' => $param['id'] ?? null],
                    [
                        'code' => $param['code'],
                        'name' => $param['name'],
                        'unit_id' => $param['unit_id'] ?? null,
                        'equipment_category_id' => $equipment->equipment_category_id,
                    ]
                );
                $keepIds[] = $record->id;
            }
        }

        $equipment->equipmentParameters()
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(fn ($parameter) => $parameter->delete());
    }
}
