<?php

namespace App\Services\Company;

use App\Models\Company;

class UpdateCompanyService
{
    /**
     * Update an existing company.
     *
     * @param  array{name?: string, contact?: string|null}  $data
     */
    public function execute(Company $company, array $data): Company
    {
        $company->update($data);

        return $company;
    }
}
