<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyResource;
use App\Models\Company;
use App\Services\Company\ShowCompanyService;
use Illuminate\Http\Request;

class ShowCompanyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Company $company, ShowCompanyService $service): CompanyResource
    {
        $company = $service->execute($company);

        return new CompanyResource($company);
    }
}
