<?php

namespace App\Builders\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<User>
 */
class UserQueryBuilder extends Builder
{
    /**
     * Scope a query to only include active users.
     */
    public function whereActive(): self
    {
        return $this->where('is_active', true);
    }

    /**
     * Filter users by department UUID(s).
     *
     * @param  string|array<int, string>  $departmentId
     */
    public function whereDepartment(string|array $departmentId): self
    {
        return $this->whereIn('department_id', (array) $departmentId);
    }

    /**
     * Filter users by company ID(s).
     *
     * @param  int|array<int, int>  $companyId
     */
    public function whereCompany(int|array $companyId): self
    {
        return $this->whereHas('department', function (Builder $query) use ($companyId) {
            $query->whereIn('company_id', (array) $companyId);
        });
    }

    /**
     * Filter users by department name.
     */
    public function whereDepartmentName(string $name): self
    {
        return $this->whereHas('department', function (Builder $query) use ($name) {
            $query->where('name', 'like', "%{$name}%");
        });
    }

    /**
     * Filter users by company name.
     */
    public function whereCompanyName(string $name): self
    {
        return $this->whereHas('department.company', function (Builder $query) use ($name) {
            $query->where('name', 'like', "%{$name}%");
        });
    }

    /**
     * Eager load the department and company relationship.
     */
    public function withDepartmentAndCompany(): self
    {
        return $this->with('department.company');
    }

    /**
     * Dynamically filter users based on an array of filters.
     *
     * @param array{
     *     department_id?: string|array<int, string>,
     *     company_id?: int|array<int, int>,
     *     department_name?: string,
     *     company_name?: string
     * } $filters
     */
    public function filter(array $filters): self
    {
        return $this->when($filters['department_id'] ?? null, function (self $query, string|array $departmentId) {
            $query->whereDepartment($departmentId);
        })
            ->when($filters['company_id'] ?? null, function (self $query, int|array $companyId) {
                $query->whereCompany($companyId);
            })
            ->when($filters['department_name'] ?? null, function (self $query, string $departmentName) {
                $query->whereDepartmentName($departmentName);
            })
            ->when($filters['company_name'] ?? null, function (self $query, string $companyName) {
                $query->whereCompanyName($companyName);
            });
    }
}
