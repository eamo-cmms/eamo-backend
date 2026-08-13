<?php

declare(strict_types=1);

namespace Modules\Equipment\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Equipment\Checklist\Models\ChecklistDetail;
use Modules\Equipment\Checklist\Models\ChecklistLog;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;
use Modules\Equipment\Checklist\Models\ChecklistSession;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;
use Modules\Masterdata\Equipment\Models\Equipment;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;
use Modules\Masterdata\Equipment\Models\EquipmentError;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;

/**
 * Delegates soft deletion to Eloquent models using CascadeSoftDeletes trait.
 */
final class EquipmentCascadeSoftDeleteService
{
    public function deleteCategory(EquipmentCategory $category): void
    {
        DB::transaction(function () use ($category): void {
            $category->delete();
        });
    }

    public function isDeletingCategory(EquipmentCategory $category): bool
    {
        return false;
    }

    public function isDeletingEquipment(Equipment $equipment): bool
    {
        return false;
    }

    public function deleteEquipment(Equipment $equipment): void
    {
        DB::transaction(function () use ($equipment): void {
            $equipment->delete();
        });
    }

    public function deleteParameter(EquipmentParameter $parameter): void
    {
        $parameter->delete();
    }

    public function deleteEquipmentError(EquipmentError $error): void
    {
        $error->delete();
    }

    public function deleteChecklistSession(ChecklistSession $session): void
    {
        DB::transaction(function () use ($session): void {
            $session->delete();
        });
    }

    public function deleteChecklistDetail(ChecklistDetail $detail): void
    {
        $detail->delete();
    }

    public function deleteChecklistSchedules(Collection $schedules): void
    {
        $schedules->each(fn (ChecklistSchedule $schedule) => $this->deleteChecklistSchedule($schedule));
    }

    public function deleteChecklistSchedule(ChecklistSchedule $schedule): void
    {
        $schedule->delete();
    }

    public function deleteChecklistLog(ChecklistLog $log): void
    {
        $log->delete();
    }

    public function deleteMaintenancePlan(MaintenancePlan $plan): void
    {
        DB::transaction(function () use ($plan): void {
            $plan->delete();
        });
    }

    public function deleteMaintenanceCategory(MaintenanceCategory $category): void
    {
        DB::transaction(function () use ($category): void {
            $category->delete();
        });
    }

    public function deleteMaintenanceItem(MaintenanceItem $item): void
    {
        $item->delete();
    }

    public function deleteMaintenanceSchedules(Collection $schedules): void
    {
        $schedules->each(fn (MaintenanceSchedule $schedule) => $this->deleteMaintenanceSchedule($schedule));
    }

    public function deleteMaintenanceSchedule(MaintenanceSchedule $schedule): void
    {
        $schedule->delete();
    }

    public function deleteErrorLog(EquipmentErrorLog $log): void
    {
        $log->delete();
    }
}
