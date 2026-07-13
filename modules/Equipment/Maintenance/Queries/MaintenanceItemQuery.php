<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;

/**
 * Dynamic query builder for eamo_maintenance_items.
 *
 * Available indexes:
 *   - PRIMARY KEY (id)
 *   - FK index on maintenance_category_id → eamo_maintenance_categories(id)
 *
 * Usage:
 *   MaintenanceItemQuery::make()
 *       ->forCategory($categoryId)
 *       ->withCategory()
 *       ->withSchedules()
 *       ->search('filter')
 *       ->paginate(50);
 */
final class MaintenanceItemQuery
{
    /** @var Builder<MaintenanceItem> */
    private Builder $query;

    /** @var array<string> */
    private array $relations = [];

    public function __construct()
    {
        $this->query = MaintenanceItem::query();
    }

    public static function make(): self
    {
        return new self;
    }

    // ─── Eager Load Toggles ──────────────────────────────────────────────────

    /**
     * Load assigned users for each item.
     */
    public function withUsers(): self
    {
        $this->relations[] = 'users';

        return $this;
    }

    /**
     * Load parent category for each item.
     * Prevents N+1 when accessing item → category.
     */
    public function withCategory(): self
    {
        $this->relations[] = 'maintenanceCategory';

        return $this;
    }

    /**
     * Load schedules associated with each item.
     * Prevents N+1 when iterating item → schedules.
     */
    public function withSchedules(): self
    {
        $this->relations[] = 'maintenanceSchedules';

        return $this;
    }

    /**
     * Load schedules + their plan in one go.
     */
    public function withSchedulesAndPlan(): self
    {
        $this->relations[] = 'maintenanceSchedules.maintenancePlan';

        return $this;
    }

    // ─── Dynamic Filters ─────────────────────────────────────────────────────

    /**
     * Filter by category — hits FK index on maintenance_category_id.
     */
    public function forCategory(string $categoryId): self
    {
        $this->query->where('maintenance_category_id', $categoryId);

        return $this;
    }

    /**
     * Filter by multiple categories at once — uses IN on the FK index.
     *
     * @param  array<string>  $categoryIds
     */
    public function forCategories(array $categoryIds): self
    {
        $this->query->whereIn('maintenance_category_id', $categoryIds);

        return $this;
    }

    /**
     * Search name and description (ilike for case-insensitive PostgreSQL).
     */
    public function search(?string $term): self
    {
        if (filled($term)) {
            $this->query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('description', 'ilike', "%{$term}%");
            });
        }

        return $this;
    }

    /**
     * Only items that have at least one schedule.
     */
    public function hasSchedules(): self
    {
        $this->query->has('maintenanceSchedules');

        return $this;
    }

    // ─── Ordering ────────────────────────────────────────────────────────────

    public function orderByName(string $direction = 'asc'): self
    {
        $this->query->orderBy('name', $direction);

        return $this;
    }

    public function latest(): self
    {
        $this->query->orderByDesc('created_at');

        return $this;
    }

    // ─── Terminal Methods ─────────────────────────────────────────────────────

    /** @return LengthAwarePaginator<MaintenanceItem> */
    public function paginate(int $perPage = 50): LengthAwarePaginator
    {
        return $this->build()->paginate($perPage);
    }

    /** @return Collection<int, MaintenanceItem> */
    public function get(): Collection
    {
        return $this->build()->get();
    }

    public function first(): ?MaintenanceItem
    {
        return $this->build()->first();
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    /** @return Builder<MaintenanceItem> */
    private function build(): Builder
    {
        if ($this->relations !== []) {
            $this->query->with(array_unique($this->relations));
        }

        return $this->query;
    }
}
