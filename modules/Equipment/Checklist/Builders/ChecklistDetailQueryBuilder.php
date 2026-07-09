<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Equipment\Checklist\Models\ChecklistDetail;

/**
 * @extends Builder<ChecklistDetail>
 */
final class ChecklistDetailQueryBuilder extends Builder
{
    /**
     * Filter by checklist ID.
     */
    public function whereChecklist(string|array $checklistId): self
    {
        return $this->whereIn('checklist_id', (array) $checklistId);
    }

    /**
     * Filter by session ID.
     */
    public function whereSession(string|array $sessionId): self
    {
        return $this->whereIn('session_id', (array) $sessionId);
    }

    /**
     * Filter by result.
     */
    public function whereResult(string|array $result): self
    {
        return $this->whereIn('result', (array) $result);
    }

    /**
     * Filter by description.
     */
    public function whereDescription(string $description): self
    {
        return $this->where('description', 'like', "%{$description}%");
    }

    /**
     * Filter by session equipment ID.
     */
    public function whereSessionEquipment(string|array $equipmentId): self
    {
        return $this->whereHas('session', function (Builder $query) use ($equipmentId) {
            $query->whereIn('equipment_id', (array) $equipmentId);
        });
    }

    /**
     * Filter by session date.
     */
    public function whereSessionDate(string $date): self
    {
        return $this->whereHas('session', function (Builder $query) use ($date) {
            $query->whereDate('session_date', $date);
        });
    }

    /**
     * Filter by session date range.
     */
    public function whereSessionDateBetween(string $startDate, string $endDate): self
    {
        return $this->whereHas('session', function (Builder $query) use ($startDate, $endDate) {
            $query->whereBetween('session_date', [$startDate, $endDate]);
        });
    }

    /**
     * Dynamically filter checklist details.
     */
    public function filter(array $filters): self
    {
        return $this->when($filters['checklist_id'] ?? null, function (self $query, $checklistId) {
            $query->whereChecklist($checklistId);
        })
            ->when($filters['session_id'] ?? null, function (self $query, $sessionId) {
                $query->whereSession($sessionId);
            })
            ->when($filters['result'] ?? null, function (self $query, $result) {
                $query->whereResult($result);
            })
            ->when($filters['description'] ?? null, function (self $query, string $description) {
                $query->whereDescription($description);
            })
            ->when($filters['equipment_id'] ?? null, function (self $query, $equipmentId) {
                $query->whereSessionEquipment($equipmentId);
            })
            ->when($filters['session_date'] ?? null, function (self $query, string $date) {
                $query->whereSessionDate($date);
            })
            ->when(($filters['start_date'] ?? null) && ($filters['end_date'] ?? null), function (self $query) use ($filters) {
                $query->whereSessionDateBetween($filters['start_date'], $filters['end_date']);
            })
            ->when(filter_var($filters['with_session'] ?? $filters['include_session'] ?? false, FILTER_VALIDATE_BOOLEAN), function (self $query) {
                $query->with('session');
            });
    }
}
