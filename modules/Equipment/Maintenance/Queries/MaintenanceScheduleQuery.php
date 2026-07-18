<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

/**
 * Dynamic query builder for eamo_maintenance_schedules.
 *
 * Available indexes:
 *   - PRIMARY KEY (id)
 *   - FK index on maintenance_plan_id → eamo_maintenance_plans(id)
 *   - FK index on maintenance_item_id → eamo_maintenance_items(id)
 *
 * Usage:
 *   MaintenanceScheduleQuery::make()
 *       ->withPlan()
 *       ->withItem()
 *       ->withUsers()
 *       ->forPlan($planId)
 *       ->forItem($itemId)
 *       ->forEquipment($equipmentId)
 *       ->dateRange('2025-01-01', '2025-12-31')
 *       ->paginate(50);
 */
final class MaintenanceScheduleQuery
{
    /** @var Builder<MaintenanceSchedule> */
    private Builder $query;

    /** @var array<string> */
    private array $relations = [];

    public function __construct()
    {
        $this->query = MaintenanceSchedule::query();
    }

    public static function make(): self
    {
        return new self;
    }

    // ─── Eager Load Toggles ──────────────────────────────────────────────────

    /**
     * Load the parent plan.
     * Prevents N+1 when iterating schedule → plan.
     */
    public function withPlan(): self
    {
        $this->relations[] = 'maintenancePlan';

        return $this;
    }

    /**
     * Load plan with its equipment and category.
     */
    public function withPlanFull(): self
    {
        $this->relations[] = 'maintenancePlan.equipment';
        $this->relations[] = 'maintenancePlan.maintenanceCategory';

        return $this;
    }

    /**
     * Load the maintenance item.
     * Prevents N+1 when iterating schedule → item.
     */
    public function withItem(): self
    {
        $this->relations[] = 'maintenanceItem';

        return $this;
    }

    /**
     * Load maintenance item with its category.
     */
    public function withItemAndCategory(): self
    {
        $this->relations[] = 'maintenanceItem.maintenanceCategory';

        return $this;
    }

    /**
     * Load assigned users (BelongsToMany via eamo_maintenance_schedule_user).
     * Prevents N+1 when iterating schedule → users.
     */
    public function withUsers(): self
    {
        $this->relations[] = 'users';

        return $this;
    }

    /**
     * Load maintenance logs for each schedule.
     */
    public function withLogs(): self
    {
        $this->relations[] = 'maintenanceLogs';

        return $this;
    }

    /**
     * Load everything needed for a detailed schedule view.
     */
    public function withFull(): self
    {
        $this->relations[] = 'maintenancePlan.equipment';
        $this->relations[] = 'maintenancePlan.maintenanceCategory';
        $this->relations[] = 'maintenanceItem.maintenanceCategory';
        $this->relations[] = 'users';
        $this->relations[] = 'maintenanceLogs';

        return $this;
    }

    // ─── Dynamic Filters ─────────────────────────────────────────────────────

    /**
     * Filter by plan — hits FK index on maintenance_plan_id.
     */
    public function forPlan(string $planId): self
    {
        $this->query->where('maintenance_plan_id', $planId);

        return $this;
    }

    /**
     * Filter by multiple plans — uses IN on the FK index.
     *
     * @param  array<string>  $planIds
     */
    public function forPlans(array $planIds): self
    {
        $this->query->whereIn('maintenance_plan_id', $planIds);

        return $this;
    }

    /**
     * Filter by maintenance item — hits FK index on maintenance_item_id.
     */
    public function forItem(string $itemId): self
    {
        $this->query->where('maintenance_item_id', $itemId);

        return $this;
    }

    /**
     * Filter by equipment_id (sequential scan unless index added).
     * Recommended: add index on equipment_id for large tables.
     */
    public function forEquipment(string $equipmentId): self
    {
        $this->query->where('equipment_id', $equipmentId);

        return $this;
    }

    public function includeTrashed(bool $only = false): self
    {
        $only ? $this->query->onlyTrashed() : $this->query->withTrashed();

        return $this;
    }

    /**
     * Filter schedules in a date range.
     * Recommended: add BRIN or BTREE index on date for time-series queries.
     */
    public function dateRange(?string $from, ?string $to): self
    {
        $this->query
            ->when(filled($from), fn (Builder $q) => $q->where('date', '>=', $from))
            ->when(filled($to), fn (Builder $q) => $q->where('date', '<=', $to));

        return $this;
    }

    /**
     * Only schedules for a specific month/year.
     */
    public function inMonth(int $year, int $month): self
    {
        $this->query
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        return $this;
    }

    /**
     * Filter by category via nested relation — uses plan FK + category FK.
     */
    public function forCategory(string $categoryId): self
    {
        $this->query->whereHas(
            'maintenancePlan',
            fn (Builder $q) => $q->where('maintenance_category_id', $categoryId),
        );

        return $this;
    }

    /**
     * Filter schedules assigned to a specific user.
     */
    public function assignedTo(string $userId): self
    {
        $this->query->whereHas(
            'users',
            fn (Builder $q) => $q->where('users.id', $userId),
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

    /** @return LengthAwarePaginator<MaintenanceSchedule> */
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->build()->paginate($perPage);
    }

    /** @return Collection<int, MaintenanceSchedule> */
    public function get(): Collection
    {
        return $this->build()->get();
    }

    public function first(): ?MaintenanceSchedule
    {
        return $this->build()->first();
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    /** @return Builder<MaintenanceSchedule> */
    private function build(): Builder
    {
        if ($this->relations !== []) {
            $this->query->with(array_unique($this->relations));
        }

        return $this->query;
    }
}
