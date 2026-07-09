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
     * @return LengthAwarePaginator
     */
    public function execute(?int $perPage = null): LengthAwarePaginator
    {
        return Company::paginate($perPage ?? 10);
    }
}
