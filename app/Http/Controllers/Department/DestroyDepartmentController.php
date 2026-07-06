<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\Department\DestroyDepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestroyDepartmentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Department $department, DestroyDepartmentService $service): JsonResponse
    {
        $service->execute($department);

        return response()->json([
            'message' => 'Department deleted successfully.',
        ]);
    }
}
