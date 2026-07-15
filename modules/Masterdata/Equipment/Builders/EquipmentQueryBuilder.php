<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Builders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Masterdata\Equipment\Models\Equipment;

/**
 * @extends Builder<Equipment>
 */
final class EquipmentQueryBuilder extends Builder
{
    /**
     * Scope a query to only include active equipment.
     */
    public function whereActive(): self
    {
        return $this->where('is_active', true);
    }

    /**
     * Filter equipment by name.
     */
    public function whereName(string $name): self
    {
        return $this->where('name', 'like', "%{$name}%");
    }

    /**
     * Filter equipment by code.
     */
    public function whereCode(string $code): self
    {
        return $this->where('code', 'like', "%{$code}%");
    }

    /**
     * Filter equipment by process UUID(s).
     *
     * @param  string|array<int, string>  $processId
     */
    public function whereProcess(string|array $processId): self
    {
        return $this->whereIn('process_id', (array) $processId);
    }

    /**
     * Filter equipment by factory UUID(s).
     *
     * @param  string|array<int, string>  $factoryId
     */
    public function whereFactory(string|array $factoryId): self
    {
        return $this->whereIn('factory_id', (array) $factoryId);
    }

    /**
     * Filter equipment by device UUID(s).
     *
     * @param  string|array<int, string>  $deviceId
     */
    public function whereDevice(string|array $deviceId): self
    {
        return $this->whereIn('device_id', (array) $deviceId);
    }

    /**
     * Filter equipment by category UUID(s).
     *
     * @param  string|array<int, string>  $categoryId
     */
    public function whereCategory(string|array $categoryId): self
    {
        return $this->whereIn('equipment_category_id', (array) $categoryId);
    }

    /**
     * Filter equipment by parent UUID(s).
     *
     * @param  string|array<int, string>  $parentId
     */
    public function whereParent(string|array $parentId): self
    {
        return $this->whereIn('parent_id', (array) $parentId);
    }

    /**
     * Filter equipment that are root nodes (have no parent).
     */
    public function whereIsRoot(): self
    {
        return $this->whereNull('parent_id');
    }

    /**
     * Filter equipment that act as a parent (have at least one child).
     */
    public function whereIsParent(): self
    {
        return $this->has('children');
    }

    /**
     * Filter equipment by category name.
     */
    public function whereCategoryName(string $name): self
    {
        return $this->whereHas('equipmentCategory', function (Builder $query) use ($name) {
            $query->where('name', 'like', "%{$name}%");
        });
    }

    /**
     * Filter equipment by parameter name.
     */
    public function whereHasParameterName(string $name): self
    {
        return $this->whereHas('equipmentParameters', function (Builder $query) use ($name) {
            $query->where('name', 'like', "%{$name}%");
        });
    }

    /**
     * Filter equipment by parameter code.
     */
    public function whereHasParameterCode(string $code): self
    {
        return $this->whereHas('equipmentParameters', function (Builder $query) use ($code) {
            $query->where('code', 'like', "%{$code}%");
        });
    }

    /**
     * Filter equipment by error ID(s).
     *
     * @param  string|array<int, string>  $errorId
     */
    public function whereHasError(string|array $errorId): self
    {
        return $this->whereHas('equipmentErrors', function (Builder $query) use ($errorId) {
            $query->whereIn('eamo_equipment_errors.id', (array) $errorId);
        });
    }

    /**
     * Filter equipment by error name.
     */
    public function whereHasErrorName(string $name): self
    {
        return $this->whereHas('equipmentErrors', function (Builder $query) use ($name) {
            $query->where('name', 'like', "%{$name}%");
        });
    }

    /**
     * Filter equipment by standard parameter ID(s).
     *
     * @param  string|array<int, string>  $standardParameterId
     */
    public function whereHasStandardParameter(string|array $standardParameterId): self
    {
        return $this->whereHas('standardParameters', function (Builder $query) use ($standardParameterId) {
            $query->whereIn('id', (array) $standardParameterId);
        });
    }

    /**
     * Dynamically filter equipment based on an array of filters.
     *
     * @param array{
     *     is_active?: bool|string,
     *     name?: string,
     *     code?: string,
     *     process_id?: string|array<int, string>,
     *     factory_id?: string|array<int, string>,
     *     device_id?: string|array<int, string>,
     *     equipment_category_id?: string|array<int, string>,
     *     parent_id?: string|array<int, string>,
     *     is_root?: bool|string,
     *     is_parent?: bool|string,
     *     equipment_category_name?: string,
     *     parameter_name?: string,
     *     parameter_code?: string,
     *     error_id?: string|array<int, string>,
     *     error_name?: string,
     *     standard_parameter_id?: string|array<int, string>,
     *     q?: string
     * } $filters
     */
    public function filter(array $filters): self
    {
        return $this->when(isset($filters['is_active']), function (self $query) use ($filters) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        })
            ->when($filters['name'] ?? null, function (self $query, string $name) {
                $query->whereName($name);
            })
            ->when($filters['code'] ?? null, function (self $query, string $code) {
                $query->whereCode($code);
            })
            ->when($filters['process_id'] ?? null, function (self $query, string|array $processId) {
                $query->whereProcess($processId);
            })
            ->when($filters['factory_id'] ?? null, function (self $query, string|array $factoryId) {
                $query->whereFactory($factoryId);
            })
            ->when($filters['device_id'] ?? null, function (self $query, string|array $deviceId) {
                $query->whereDevice($deviceId);
            })
            ->when($filters['equipment_category_id'] ?? null, function (self $query, string|array $categoryId) {
                $query->whereCategory($categoryId);
            })
            ->when($filters['parent_id'] ?? null, function (self $query, string|array $parentId) {
                $query->whereParent($parentId);
            })
            ->when(isset($filters['is_root']), function (self $query) use ($filters) {
                if (filter_var($filters['is_root'], FILTER_VALIDATE_BOOLEAN)) {
                    $query->whereIsRoot();
                } else {
                    $query->whereNotNull('parent_id');
                }
            })
            ->when(isset($filters['is_parent']), function (self $query) use ($filters) {
                if (filter_var($filters['is_parent'], FILTER_VALIDATE_BOOLEAN)) {
                    $query->whereIsParent();
                } else {
                    $query->doesntHave('children');
                }
            })
            ->when($filters['equipment_category_name'] ?? null, function (self $query, string $categoryName) {
                $query->whereCategoryName($categoryName);
            })
            ->when($filters['parameter_name'] ?? null, function (self $query, string $parameterName) {
                $query->whereHasParameterName($parameterName);
            })
            ->when($filters['parameter_code'] ?? null, function (self $query, string $parameterCode) {
                $query->whereHasParameterCode($parameterCode);
            })
            ->when($filters['error_id'] ?? null, function (self $query, string|array $errorId) {
                $query->whereHasError($errorId);
            })
            ->when($filters['error_name'] ?? null, function (self $query, string $errorName) {
                $query->whereHasErrorName($errorName);
            })
            ->when($filters['standard_parameter_id'] ?? null, function (self $query, string|array $standardParameterId) {
                $query->whereHasStandardParameter($standardParameterId);
            })
            ->when($filters['q'] ?? null, function (self $query, string $q) {
                $query->where(function (self $subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            });
    }

    /**
     * Eager load checklist sessions and details, along with details count.
     */
    public function withChecklistSessionsAndDetails(): self
    {
        return $this->with(['checklistSessions.details'])
            ->withCount('checklistDetails');
    }
}
