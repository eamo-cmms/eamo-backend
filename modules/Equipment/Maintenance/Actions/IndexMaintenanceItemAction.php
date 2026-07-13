<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Queries\MaintenanceItemQuery;

final class IndexMaintenanceItemAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = MaintenanceItemQuery::make()
            ->withCategory()
            ->withUsers()
            ->search($request->input('q'));

        if ($request->filled('maintenance_category_id')) {
            $query->forCategory($request->input('maintenance_category_id'));
        }

        if ($request->boolean('with_schedules')) {
            $query->withSchedules();
        }

        if ($request->boolean('has_schedules')) {
            $query->hasSchedules();
        }

        $items = $query
            ->orderByName()
            ->paginate($request->integer('per_page', 50));

        return response()->json($items);
    }
}
