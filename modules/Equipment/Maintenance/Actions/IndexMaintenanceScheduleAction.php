<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Queries\MaintenanceScheduleQuery;

final class IndexMaintenanceScheduleAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = MaintenanceScheduleQuery::make()
            ->withPlanFull()
            ->withItem()
            ->withUsers();

        if ($request->filled('maintenance_plan_id')) {
            $query->forPlan($request->input('maintenance_plan_id'));
        }

        if ($request->filled('equipment_id')) {
            $query->forEquipment($request->input('equipment_id'));
        }

        if ($request->filled('maintenance_item_id')) {
            $query->forItem($request->input('maintenance_item_id'));
        }

        if ($request->filled('maintenance_category_id')) {
            $query->forCategory($request->input('maintenance_category_id'));
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->input('date_from'), $request->input('date_to'));
        }

        if ($request->filled('year') && $request->filled('month')) {
            $query->inMonth((int) $request->input('year'), (int) $request->input('month'));
        }

        if ($request->filled('user_id')) {
            $query->assignedTo($request->input('user_id'));
        }

        if ($request->boolean('with_logs')) {
            $query->withLogs();
        }

        if ($request->boolean('only_trashed')) {
            $query->includeTrashed(only: true);
        } elseif ($request->boolean('with_trashed')) {
            $query->includeTrashed();
        }

        $schedules = $query
            ->orderByDate()
            ->paginate($request->integer('per_page', 50));

        return response()->json($schedules);
    }
}
