<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Equipment\Checklist\Models\ChecklistSession;

/**
 * @extends Builder<ChecklistSession>
 */
final class ChecklistSessionQueryBuilder extends Builder
{
    /**
     * Filter by name.
     */
    public function whereName(string $name): self
    {
        return $this->where('name', 'like', "%{$name}%");
    }

    /**
     * Filter by equipment ID.
     */
    public function whereEquipment(string|array $equipmentId): self
    {
        return $this->whereIn('equipment_id', (array) $equipmentId);
    }

    /**
     * Filter by session date.
     */
    public function whereSessionDate(string $date): self
    {
        return $this->whereDate('session_date', $date);
    }

    /**
     * Filter by session date range.
     */
    public function whereSessionDateBetween(string $startDate, string $endDate): self
    {
        return $this->whereBetween('session_date', [$startDate, $endDate]);
    }

    /**
     * Filter by user ID(s) associated with the session.
     */
    public function whereCreatedBy(string|array $createdBy): self
    {
        return $this->whereHas('users', function (Builder $query) use ($createdBy) {
            $query->whereIn('users.id', (array) $createdBy);
        });
    }

    /**
     * Filter by checklist ID inside details.
     */
    public function whereHasChecklistId(string|array $checklistId): self
    {
        return $this->whereHas('details', function (Builder $query) use ($checklistId) {
            $query->whereIn('checklist_id', (array) $checklistId);
        });
    }

    /**
     * Filter by checklist result.
     */
    public function whereHasResult(string|array $result): self
    {
        return $this->whereHas('details.logs', function (Builder $query) use ($result) {
            $query->whereIn('result', (array) $result);
        });
    }

    /**
     * Dynamically filter sessions based on filters array.
     */
    public function filter(array $filters): self
    {
        if (filter_var($filters['only_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->onlyTrashed();
        } elseif (filter_var($filters['with_trashed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->withTrashed();
        }

        return $this->when($filters['name'] ?? null, function (self $query, string $name) {
            $query->whereName($name);
        })
            ->when($filters['equipment_id'] ?? null, function (self $query, $equipmentId) {
                $query->whereEquipment($equipmentId);
            })
            ->when($filters['session_date'] ?? null, function (self $query, string $date) {
                $query->whereSessionDate($date);
            })
            ->when(($filters['start_date'] ?? null) && ($filters['end_date'] ?? null), function (self $query) use ($filters) {
                $query->where(function (self $subQuery) use ($filters) {
                    $subQuery->whereBetween('session_date', [$filters['start_date'], $filters['end_date']])
                        ->orWhereHas('schedules', function ($q) use ($filters) {
                            $q->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
                        });
                });
            })
            ->when($filters['created_by'] ?? null, function (self $query, $createdBy) {
                $query->whereCreatedBy($createdBy);
            })
            ->when($filters['checklist_id'] ?? null, function (self $query, $checklistId) {
                $query->whereHasChecklistId($checklistId);
            })
            ->when($filters['result'] ?? null, function (self $query, $result) {
                $query->whereHasResult($result);
            })
            ->when($filters['q'] ?? null, function (self $query, string $q) {
                $query->where(function (self $subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('equipment_id', 'like', "%{$q}%")
                        ->orWhereHas('users', function (Builder $userQuery) use ($q) {
                            $userQuery->where('name', 'like', "%{$q}%");
                        });
                });
            })
            ->when(filter_var($filters['with_details'] ?? $filters['include_details'] ?? false, FILTER_VALIDATE_BOOLEAN), function (self $query) {
                $query->with('details');
            });
    }
}
