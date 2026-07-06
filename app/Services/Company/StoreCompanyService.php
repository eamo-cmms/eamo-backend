<?php

namespace App\Services\Company;

use App\Models\Company;

class StoreCompanyService
{
    /**
     * Store a new company.
     *
     * @param  array{name: string, contact?: string|null}  $data
     */
    public function execute(array $data): Company
    {
        return Company::create($data);
    }
}
