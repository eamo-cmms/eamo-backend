<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Company\DestroyCompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Gate;

class DestroyCompanyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Company $company, DestroyCompanyService $service): JsonResponse
    {
        Gate::authorize('delete', $company);

        $service->execute($company);

        return response()->json([
            'message' => __('company.deleted'),
        ]);
    }
}
