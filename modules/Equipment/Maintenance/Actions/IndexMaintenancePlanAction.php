<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Queries\MaintenancePlanQuery;

final class IndexMaintenancePlanAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = MaintenancePlanQuery::make()
            ->withEquipment()
            ->withCategory()
            ->search($request->input('q'));

        if ($request->filled('equipment_id')) {
            $query->forEquipment($request->input('equipment_id'));
        }

        if ($request->filled('maintenance_category_id')) {
            $query->forCategory($request->input('maintenance_category_id'));
        }

        if ($request->filled('maintenance_type')) {
            $query->ofType($request->input('maintenance_type'));
        }

        if ($request->boolean('has_cycle')) {
            $query->hasCycleType();
        } elseif ($request->boolean('is_manual')) {
            $query->isManual();
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->input('date_from'), $request->input('date_to'));
        }

        if ($request->boolean('with_schedules')) {
            $query->withSchedulesAndItems();
        }

        if ($request->boolean('only_trashed')) {
            $query->includeTrashed(only: true);
        } elseif ($request->boolean('with_trashed')) {
            $query->includeTrashed();
        }

        $plans = $query
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($plans);
    }
}
