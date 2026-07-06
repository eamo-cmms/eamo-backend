<?php

namespace App\Services\Company;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class ListCompaniesService
{
    /**
     * Get a list of all companies.
     *
     * @return Collection<int, Company>
     */
    public function execute(): Collection
    {
        return Company::all();
    }
}
