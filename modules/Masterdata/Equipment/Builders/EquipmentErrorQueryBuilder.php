<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Masterdata\Equipment\Models\EquipmentError;

/**
 * @extends Builder<EquipmentError>
 */
final class EquipmentErrorQueryBuilder extends Builder
{
    /**
     * Filter by name.
     */
    public function whereName(string $name): self
    {
        return $this->where('name', 'like', "%{$name}%");
    }

    /**
     * Filter by reason.
     */
    public function whereReason(string $reason): self
    {
        return $this->where('reason', 'like', "%{$reason}%");
    }

    /**
     * Filter by fix.
     */
    public function whereFix(string $fix): self
    {
        return $this->where('fix', 'like', "%{$fix}%");
    }

    /**
     * Filter by protection measures.
     */
    public function whereProtectionMeasures(string $protectionMeasures): self
    {
        return $this->where('protection_measures', 'like', "%{$protectionMeasures}%");
    }

    /**
     * Filter by associated equipment ID(s).
     *
     * @param  string|array<int, string>  $equipmentId
     */
    public function whereHasEquipment(string|array $equipmentId): self
    {
        return $this->whereHas('equipment', function (Builder $query) use ($equipmentId) {
            $query->whereIn('eamo_equipment.id', (array) $equipmentId);
        });
    }

    /**
     * Dynamically filter equipment errors based on an array of filters.
     *
     * @param array{
     *     name?: string,
     *     reason?: string,
     *     fix?: string,
     *     protection_measures?: string,
     *     equipment_id?: string|array<int, string>,
     *     q?: string
     * } $filters
     */
    public function filter(array $filters): self
    {
        return $this->when($filters['name'] ?? null, function (self $query, string $name) {
            $query->whereName($name);
        })
            ->when($filters['reason'] ?? null, function (self $query, string $reason) {
                $query->whereReason($reason);
            })
            ->when($filters['fix'] ?? null, function (self $query, string $fix) {
                $query->whereFix($fix);
            })
            ->when($filters['protection_measures'] ?? null, function (self $query, string $protectionMeasures) {
                $query->whereProtectionMeasures($protectionMeasures);
            })
            ->when($filters['equipment_id'] ?? null, function (self $query, string|array $equipmentId) {
                $query->whereHasEquipment($equipmentId);
            })
            ->when($filters['q'] ?? null, function (self $query, string $q) {
                $query->where(function (self $subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('reason', 'like', "%{$q}%")
                        ->orWhere('fix', 'like', "%{$q}%")
                        ->orWhere('protection_measures', 'like', "%{$q}%");
                });
            });
    }
}
