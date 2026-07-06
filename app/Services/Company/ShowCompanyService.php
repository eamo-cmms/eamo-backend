<?php

namespace App\Services\Company;

use App\Models\Company;

class ShowCompanyService
{
    /**
     * Retrieve a company, loading relationships.
     */
    public function execute(Company $company): Company
    {
        return $company->loadMissing('departments');
    }
}
