<?php

namespace App\Services\Company;

use App\Models\Company;

class DestroyCompanyService
{
    /**
     * Delete a company.
     */
    public function execute(Company $company): bool
    {
        return (bool) $company->delete();
    }
}
