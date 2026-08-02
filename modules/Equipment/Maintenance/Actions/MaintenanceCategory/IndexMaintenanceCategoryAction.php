<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceCategory;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Queries\MaintenanceCategoryQuery;

final class IndexMaintenanceCategoryAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = MaintenanceCategoryQuery::make()
            ->withItems()
            ->search($request->input('q'));

        if ($request->boolean('with_plans')) {
            $query->withPlans();
        }

        if ($request->boolean('has_items')) {
            $query->hasItems();
        }

        if ($request->boolean('only_trashed')) {
            $query->includeTrashed(only: true);
        } elseif ($request->boolean('with_trashed')) {
            $query->includeTrashed();
        }

        $categories = $query
            ->orderByName()
            ->paginate($request->integer('per_page', 15));

        return response()->json($categories);
    }
}
