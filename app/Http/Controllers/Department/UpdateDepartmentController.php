<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\Department\DepartmentResource;
use App\Models\Department;
use App\Services\Department\UpdateDepartmentService;

class UpdateDepartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateDepartmentRequest $request, Department $department, UpdateDepartmentService $service): DepartmentResource
    {
        $department = $service->execute($department, $request->validated());

        return new DepartmentResource($department);
    }
}
