<?php

namespace App\Services\Department;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;

class ListDepartmentsService
{
    /**
     * Get a list of all departments.
     *
     * @return Collection<int, Department>
     */
    public function execute(): Collection
    {
        return Department::all();
    }
}
