<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\Department\DestroyDepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Gate;

class DestroyDepartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Department $department, DestroyDepartmentService $service): JsonResponse
    {
        Gate::authorize('delete', $department);

        $service->execute($department);

        return response()->json([
            'message' => __('department.deleted'),
        ]);
    }
}
