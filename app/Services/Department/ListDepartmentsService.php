<?php

namespace App\Services\Department;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDepartmentsService
{
    /**
     * Get a list of all departments.
     *
     * @param int|null $perPage
     * @return LengthAwarePaginator
     */
    public function execute(?int $perPage = null): LengthAwarePaginator
    {
        return Department::with('company')->paginate($perPage ?? 10);
    }
}
