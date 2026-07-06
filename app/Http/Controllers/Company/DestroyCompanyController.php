<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Company\DestroyCompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestroyCompanyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Company $company, DestroyCompanyService $service): JsonResponse
    {
        $service->execute($company);

        return response()->json([
            'message' => 'Company deleted successfully.',
        ]);
    }
}
