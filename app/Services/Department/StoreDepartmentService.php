<?php

namespace App\Services\Department;

use App\Models\Department;

class StoreDepartmentService
{
    /**
     * Store a new department.
     *
     * @param  array{company_id: int, name: string, contact?: string|null}  $data
     */
    public function execute(array $data): Department
    {
        return Department::create($data);
    }
}
