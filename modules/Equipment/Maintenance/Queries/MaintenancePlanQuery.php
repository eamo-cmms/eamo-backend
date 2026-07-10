<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

/**
 * Dynamic query builder for eamo_maintenance_plans.
 *
 * Available indexes:
 *   - PRIMARY KEY (id)
 *   - FK index on equipment_id → eamo_equipment(id)
 *   - FK index on maintenance_category_id → eamo_maintenance_categories(id)
 *
 * Usage:
 *   MaintenancePlanQuery::make()
 *       ->withEquipment()
 *       ->withCategory()
 *       ->withSchedules()
 *       ->forEquipment($equipmentId)
 *       ->forCategory($categoryId)
 *       ->hasCycleType()
 *       ->dateRange('2025-01-01', '2025-12-31')
 *       ->search('MP-001')
 *       ->paginate(15);
 */
final class MaintenancePlanQuery
{
    /** @var Builder<MaintenancePlan> */
    private Builder $query;

    /** @var array<string> */
    private array $relations = [];

    public function __construct()
    {
        $this->query = MaintenancePlan::query();
    }

    public static function make(): self
    {
        return new self;
    }

    // ─── Eager Load Toggles ──────────────────────────────────────────────────

    /**
     * Load equipment for each plan.
     * Prevents N+1 when iterating plan → equipment.
     */
    public function withEquipment(): self
    {
        $this->relations[] = 'equipment';

        return $this;
    }

    /**
     * Load maintenance category for each plan.
     */
    public function withCategory(): self
    {
        $this->relations[] = 'maintenanceCategory';

        return $this;
    }

    /**
     * Load category together with its items.
     */
    public function withCategoryAndItems(): self
    {
        $this->relations[] = 'maintenanceCategory.maintenanceItems';

        return $this;
    }

    /**
     * Load all schedules for each plan.
     * Prevents N+1 when iterating plan → schedules.
     */
    public function withSchedules(): self
    {
        $this->relations[] = 'maintenanceSchedule';

        return $this;
    }

    /**
     * Load schedules together with their maintenance item.
     */
    public function withSchedulesAndItems(): self
    {
        $this->relations[] = 'maintenanceSchedule.maintenanceItem';

        return $this;
    }

    /**
     * Load schedules, their items, and assigned users.
     * Full data set for the plan detail view.
     */
    public function withSchedulesFull(): self
    {
        $this->relations[] = 'maintenanceSchedule.maintenanceItem';
        $this->relations[] = 'maintenanceSchedule.users';

        return $this;
    }

    /**
     * Load users directly assigned to the plan.
     */
    public function withUsers(): self
    {
        $this->relations[] = 'users';

        return $this;
    }

    // ─── Dynamic Filters ─────────────────────────────────────────────────────

    /**
     * Filter by equipment — hits FK index on equipment_id.
     */
    public function forEquipment(string $equipmentId): self
    {
        $this->query->where('equipment_id', $equipmentId);

        return $this;
    }

    /**
     * Filter by maintenance category — hits FK index on maintenance_category_id.
     */
    public function forCategory(string $categoryId): self
    {
        $this->query->where('maintenance_category_id', $categoryId);

        return $this;
    }

    /**
     * Filter by maintenance type (preventive / corrective …).
     */
    public function ofType(string $type): self
    {
        $this->query->where('maintenance_type', $type);

        return $this;
    }

    /**
     * Only plans that use automatic scheduling (cycle_type is set).
     */
    public function hasCycleType(): self
    {
        $this->query->whereNotNull('cycle_type');

        return $this;
    }

    /**
     * Only plans with manual schedules (no cycle_type).
     */
    public function isManual(): self
    {
        $this->query->whereNull('cycle_type');

        return $this;
    }

    /**
     * Filter plans whose date falls within a range.
     * Uses sequential scan unless a date index is added — still efficient for
     * small-to-medium tables.
     */
    public function dateRange(?string $from, ?string $to): self
    {
        $this->query
            ->when(filled($from), fn (Builder $q) => $q->where('date', '>=', $from))
            ->when(filled($to), fn (Builder $q) => $q->where('date', '<=', $to));

        return $this;
    }

    /**
     * Search plan_code via ILIKE.
     * Consider adding a GIN/pg_trgm index on plan_code for large datasets.
     */
    public function search(?string $term): self
    {
        if (filled($term)) {
            $this->query->where('plan_code', 'ilike', "%{$term}%");
        }

        return $this;
    }

    /**
     * Filter plans that have at least one schedule for the given item.
     * Uses the FK index on maintenance_plan_id inside schedules.
     */
    public function hasItemScheduled(string $maintenanceItemId): self
    {
        $this->query->whereHas(
            'maintenanceSchedule',
            fn (Builder $q) => $q->where('maintenance_item_id', $maintenanceItemId),
        );

        return $this;
    }

    // ─── Ordering ────────────────────────────────────────────────────────────

    public function orderByDate(string $direction = 'asc'): self
    {
        $this->query->orderBy('date', $direction);

        return $this;
    }

    public function latest(): self
    {
        $this->query->orderByDesc('created_at');

        return $this;
    }

    // ─── Terminal Methods ─────────────────────────────────────────────────────

    /** @return LengthAwarePaginator<MaintenancePlan> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->build()->paginate($perPage);
    }

    /** @return Collection<int, MaintenancePlan> */
    public function get(): Collection
    {
        return $this->build()->get();
    }

    public function first(): ?MaintenancePlan
    {
        return $this->build()->first();
    }

    public function find(string $id): ?MaintenancePlan
    {
        return $this->build()->find($id);
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    /** @return Builder<MaintenancePlan> */
    private function build(): Builder
    {
        if ($this->relations !== []) {
            $this->query->with(array_unique($this->relations));
        }

        return $this->query;
    }
}
