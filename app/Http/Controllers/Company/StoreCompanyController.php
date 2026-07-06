<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Resources\Company\CompanyResource;
use App\Services\Company\StoreCompanyService;

class StoreCompanyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreCompanyRequest $request, StoreCompanyService $service): CompanyResource
    {
        $company = $service->execute($request->validated());

        return new CompanyResource($company);
    }
}
