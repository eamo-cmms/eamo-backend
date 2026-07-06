<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\Department\DepartmentResource;
use App\Services\Department\ListDepartmentsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexDepartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ListDepartmentsService $service): AnonymousResourceCollection
    {
        $departments = $service->execute();

        return DepartmentResource::collection($departments);
    }
}
