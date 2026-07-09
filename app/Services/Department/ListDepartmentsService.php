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
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(?int $perPage = null, array $filters = []): LengthAwarePaginator
    {
        $query = Department::with('company');

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage ?? 10);
    }
}
