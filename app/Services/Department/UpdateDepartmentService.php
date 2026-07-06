<?php

namespace App\Services\Department;

use App\Models\Department;

class UpdateDepartmentService
{
    /**
     * Update an existing department.
     *
     * @param  array{company_id?: int, name?: string, contact?: string|null}  $data
     */
    public function execute(Department $department, array $data): Department
    {
        $department->update($data);

        return $department;
    }
}
