<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\Department\DepartmentResource;
use App\Models\Department;
use App\Services\Department\ShowDepartmentService;
use Illuminate\Http\Request;

class ShowDepartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Department $department, ShowDepartmentService $service): DepartmentResource
    {
        $department = $service->execute($department);

        return new DepartmentResource($department);
    }
}
