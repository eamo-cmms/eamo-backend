<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\Company\CompanyResource;
use App\Models\Company;
use App\Services\Company\UpdateCompanyService;

class UpdateCompanyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateCompanyRequest $request, Company $company, UpdateCompanyService $service): CompanyResource
    {
        $company = $service->execute($company, $request->validated());

        return new CompanyResource($company);
    }
}
