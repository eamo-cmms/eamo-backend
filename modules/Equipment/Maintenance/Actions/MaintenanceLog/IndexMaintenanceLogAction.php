<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Actions\MaintenanceLog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

final class IndexMaintenanceLogAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $query = MaintenanceLog::query()
            ->with([
                'equipment' => function ($q) {
                    $q->select(['id', 'code', 'name', 'equipment_category_id', 'is_active'])
                      ->with('equipmentCategory:id,code,name');
                },
                'user:id,name,email',
                'maintenanceSchedule' => function ($q) {
                    $q->select(['id', 'equipment_id', 'maintenance_plan_id', 'date', 'is_rescheduled'])
                      ->with('maintenancePlan:id,plan_code,maintenance_type');
                },
            ])
            ->filter($request->all())
            ->orderByDesc('log_date')
            ->orderByDesc('created_at');

        if ($request->boolean('all') || $request->input('paginate') === 'false') {
            return response()->json($query->get());
        }

        $perPage = $request->integer('per_page', 15);

        return response()->json($query->paginate($perPage));
    }
}
