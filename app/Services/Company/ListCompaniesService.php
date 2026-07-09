<?php

namespace App\Services\Company;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCompaniesService
{
    /**
     * Get a list of all companies.
     *
     * @param int|null $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function execute(?int $perPage = null, array $filters = []): LengthAwarePaginator
    {
        $query = Company::query();

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
