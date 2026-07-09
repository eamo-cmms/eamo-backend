<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyResource;
use App\Services\Company\ListCompaniesService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexCompanyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ListCompaniesService $service): AnonymousResourceCollection
    {
        $companies = $service->execute(
            $request->integer('per_page', 10),
            $request->only(['search'])
        );

        return CompanyResource::collection($companies);
    }
}
