<?php

namespace App\Services\Department;

use App\Models\Department;

class DestroyDepartmentService
{
    /**
     * Delete a department.
     */
    public function execute(Department $department): bool
    {
        return (bool) $department->delete();
    }
}
