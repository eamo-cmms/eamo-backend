<?php

namespace App\Services\Department;

use App\Models\Department;

class ShowDepartmentService
{
    /**
     * Retrieve a department, loading relationships.
     */
    public function execute(Department $department): Department
    {
        return $department->loadMissing('company');
    }
}
