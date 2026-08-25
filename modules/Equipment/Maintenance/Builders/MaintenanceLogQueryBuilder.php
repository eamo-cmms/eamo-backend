<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

/**
 * @extends Builder<MaintenanceLog>
 */
final class MaintenanceLogQueryBuilder extends Builder
{
    /**
     * Filter by equipment ID(s).
     */
    public function whereEquipment(string|array $equipmentId): self
    {
        return $this->whereIn('equipment_id', (array) $equipmentId);
    }

    /**
     * Filter by specific log date.
     */
    public function whereLogDate(string $date): self
    {
        return $this->whereDate('log_date', $date);
    }

    /**
     * Filter by date from.
     */
    public function whereDateFrom(string $dateFrom): self
    {
        return $this->whereDate('log_date', '>=', $dateFrom);
    }

    /**
     * Filter by date to.
     */
    public function whereDateTo(string $dateTo): self
    {
        return $this->whereDate('log_date', '<=', $dateTo);
    }

    /**
     * Filter by log date range.
     */
    public function whereLogDateBetween(string $startDate, string $endDate): self
    {
        return $this->whereBetween('log_date', [$startDate, $endDate]);
    }

    /**
     * Filter by user ID(s).
     */
    public function whereUser(string|array $userId): self
    {
        return $this->whereIn('user_id', (array) $userId);
    }

    /**
     * Filter by maintenance type(s).
     */
    public function whereType(string|array $type): self
    {
        return $this->whereIn('type', (array) $type);
    }

    /**
     * Filter by maintenance schedule ID(s).
     */
    public function whereMaintenanceSchedule(string|array $scheduleId): self
    {
        return $this->whereIn('maintenance_schedule_id', (array) $scheduleId);
    }

    /**
     * Universal search across multiple fields (code, name, note, type, user name, user email).
     */
    public function search(string $keyword): self
    {
        $term = trim($keyword);
        if ($term === '') {
            return $this;
        }

        return $this->where(function (Builder $query) use ($term) {
            $query->where('note', 'like', "%{$term}%")
                ->orWhere('type', 'like', "%{$term}%")
                ->orWhereHas('equipment', function (Builder $eqQuery) use ($term) {
                    $eqQuery->where('code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%");
                })
                ->orWhereHas('user', function (Builder $uQuery) use ($term) {
                    $uQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Dynamically filter logs based on an array of filters.
     *
     * @param array<string, mixed> $filters
     */
    public function filter(array $filters): self
    {
        if (filter_var($filters['only_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->onlyTrashed();
        } elseif (filter_var($filters['with_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->withTrashed();
        }

        return $this
            ->when($filters['equipment_id'] ?? null, function (self $query, $equipmentId) {
                $query->whereEquipment($equipmentId);
            })
            ->when($filters['user_id'] ?? null, function (self $query, $userId) {
                $query->whereUser($userId);
            })
            ->when($filters['maintenance_schedule_id'] ?? null, function (self $query, $scheduleId) {
                $query->whereMaintenanceSchedule($scheduleId);
            })
            ->when($filters['type'] ?? null, function (self $query, $type) {
                $query->whereType($type);
            })
            ->when($filters['log_date'] ?? null, function (self $query, string $date) {
                $query->whereLogDate($date);
            })
            ->when($filters['date_from'] ?? null, function (self $query, string $dateFrom) {
                $query->whereDateFrom($dateFrom);
            })
            ->when($filters['date_to'] ?? null, function (self $query, string $dateTo) {
                $query->whereDateTo($dateTo);
            })
            ->when(($filters['start_date'] ?? null) && ($filters['end_date'] ?? null), function (self $query) use ($filters) {
                $query->whereLogDateBetween($filters['start_date'], $filters['end_date']);
            })
            ->when($filters['search'] ?? null, function (self $query, string $keyword) {
                $query->search($keyword);
            });
    }
}
