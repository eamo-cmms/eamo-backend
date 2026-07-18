<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;

/**
 * Dynamic query builder for eamo_maintenance_categories.
 *
 * Available indexes:
 *   - PRIMARY KEY (id)
 *
 * Usage:
 *   MaintenanceCategoryQuery::make()
 *       ->withItems()
 *       ->withPlans()
 *       ->search('oil')
 *       ->paginate(15);
 */
final class MaintenanceCategoryQuery
{
    /** @var Builder<MaintenanceCategory> */
    private Builder $query;

    /** @var array<string> */
    private array $relations = [];

    public function __construct()
    {
        $this->query = MaintenanceCategory::query();
    }

    public static function make(): self
    {
        return new self;
    }

    // ─── Eager Load Toggles ──────────────────────────────────────────────────

    /**
     * Load maintenance items belonging to each category.
     * Prevents N+1 when iterating category → items.
     */
    public function withItems(): self
    {
        $this->relations[] = 'maintenanceItems.users';

        return $this;
    }

    /**
     * Load maintenance plans belonging to each category.
     * Prevents N+1 when iterating category → plans.
     */
    public function withPlans(): self
    {
        $this->relations[] = 'maintenancePlans';

        return $this;
    }

    /**
     * Load plans together with their equipment.
     */
    public function withPlansAndEquipment(): self
    {
        $this->relations[] = 'maintenancePlans.equipment';

        return $this;
    }

    // ─── Dynamic Filters ─────────────────────────────────────────────────────

    /**
     * Full-text search on name and description columns.
     * No dedicated index — acceptable for low cardinality category tables.
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
     * Filter by exact name.
     */
    public function whereName(string $name): self
    {
        $this->query->where('name', $name);

        return $this;
    }

    /**
     * Filter categories that have at least one maintenance item.
     */
    public function hasItems(): self
    {
        $this->query->has('maintenanceItems');

        return $this;
    }

    public function includeTrashed(bool $only = false): self
    {
        $only ? $this->query->onlyTrashed() : $this->query->withTrashed();

        return $this;
    }

    /**
     * Filter categories that have at least one maintenance plan.
     */
    public function hasPlans(): self
    {
        $this->query->has('maintenancePlans');

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

    /** @return LengthAwarePaginator<MaintenanceCategory> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->build()->paginate($perPage);
    }

    /** @return Collection<int, MaintenanceCategory> */
    public function get(): Collection
    {
        return $this->build()->get();
    }

    public function first(): ?MaintenanceCategory
    {
        return $this->build()->first();
    }

    // ─── Internal ─────────────────────────────────────────────────────────────

    /** @return Builder<MaintenanceCategory> */
    private function build(): Builder
    {
        if ($this->relations !== []) {
            $this->query->with(array_unique($this->relations));
        }

        return $this->query;
    }
}
