<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Resources\Department\DepartmentResource;
use App\Services\Department\StoreDepartmentService;

class StoreDepartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreDepartmentRequest $request, StoreDepartmentService $service): DepartmentResource
    {
        $department = $service->execute($request->validated());

        return new DepartmentResource($department);
    }
}
