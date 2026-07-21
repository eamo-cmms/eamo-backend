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
 * Soft-deletes the complete equipment graph in dependency order.
 *
 * Database foreign keys intentionally use RESTRICT: a SQL cascade only runs on
 * physical deletes and would otherwise bypass deleted_at on descendants.
 */
final class EquipmentCascadeSoftDeleteService
{
    /** @var array<string, bool> */
    private static array $deletingCategories = [];

    /** @var array<string, bool> */
    private static array $deletingEquipment = [];

    public function deleteCategory(EquipmentCategory $category): void
    {
        if (isset(self::$deletingCategories[$category->getKey()])) {
            return;
        }

        self::$deletingCategories[$category->getKey()] = true;

        try {
            DB::transaction(function () use ($category): void {
                $category->equipment()->get()->each(fn (Equipment $equipment) => $this->deleteEquipment($equipment));
                $category->equipmentParameters()->get()->each(fn (EquipmentParameter $parameter) => $this->deleteParameter($parameter));
                $category->delete();
            });
        } finally {
            unset(self::$deletingCategories[$category->getKey()]);
        }
    }

    public function isDeletingCategory(EquipmentCategory $category): bool
    {
        return isset(self::$deletingCategories[$category->getKey()]);
    }

    public function isDeletingEquipment(Equipment $equipment): bool
    {
        return isset(self::$deletingEquipment[$equipment->getKey()]);
    }

    public function deleteEquipment(Equipment $equipment): void
    {
        if (isset(self::$deletingEquipment[$equipment->getKey()])) {
            return;
        }

        self::$deletingEquipment[$equipment->getKey()] = true;

        try {
            DB::transaction(function () use ($equipment): void {
                $equipment->children()->get()->each(fn (Equipment $child) => $this->deleteEquipment($child));

                $equipment->checklistSessions()->get()->each(fn (ChecklistSession $session) => $this->deleteChecklistSession($session));
                ChecklistSchedule::query()->where('equipment_id', $equipment->id)->get()
                    ->each(fn (ChecklistSchedule $schedule) => $this->deleteChecklistSchedule($schedule));

                $equipment->maintenancePlans()->get()->each(fn (MaintenancePlan $plan) => $this->deleteMaintenancePlan($plan));
                MaintenanceSchedule::query()->where('equipment_id', $equipment->id)->get()
                    ->each(fn (MaintenanceSchedule $schedule) => $this->deleteMaintenanceSchedule($schedule));

                $equipment->equipmentParameters()->get()->each(fn (EquipmentParameter $parameter) => $this->deleteParameter($parameter));
                $equipment->parameterLogs()->get()->each->delete();
                $equipment->errorLogs()->get()->each(fn (EquipmentErrorLog $log) => $this->deleteErrorLog($log));
                $equipment->operatingTimes()->get()->each->delete();
                $equipment->equipmentState?->delete();
                $equipment->equipmentImages()->get()->each->delete();

                $this->softDeletePivot('eamo_equipment_equipment_errors', 'equipment_id', $equipment->id);
                $equipment->delete();
            });
        } finally {
            unset(self::$deletingEquipment[$equipment->getKey()]);
        }
    }

    public function deleteParameter(EquipmentParameter $parameter): void
    {
        EquipmentParameterLog::query()
            ->where('equipment_parameter_id', $parameter->id)
            ->get()
            ->each->delete();

        $parameter->delete();
    }

    public function deleteEquipmentError(EquipmentError $error): void
    {
        EquipmentErrorLog::query()
            ->where('equipment_error_id', $error->id)
            ->get()
            ->each(fn (EquipmentErrorLog $log) => $this->deleteErrorLog($log));

        $this->softDeletePivot('eamo_equipment_equipment_errors', 'equipment_error_id', $error->id);
        $error->delete();
    }

    public function deleteChecklistSession(ChecklistSession $session): void
    {
        $session->schedules()->get()->each(fn (ChecklistSchedule $schedule) => $this->deleteChecklistSchedule($schedule));
        $session->details()->get()->each(fn (ChecklistDetail $detail) => $this->deleteChecklistDetail($detail));
        $this->softDeletePivot('eamo_checklist_session_users', 'checklist_session_id', $session->id);
        $session->delete();
    }

    public function deleteChecklistDetail(ChecklistDetail $detail): void
    {
        $detail->schedules()->get()->each(fn (ChecklistSchedule $schedule) => $this->deleteChecklistSchedule($schedule));
        $detail->delete();
    }

    public function deleteChecklistSchedules(Collection $schedules): void
    {
        $schedules->each(fn (ChecklistSchedule $schedule) => $this->deleteChecklistSchedule($schedule));
    }

    public function deleteChecklistSchedule(ChecklistSchedule $schedule): void
    {
        $schedule->logs()->get()->each(fn (ChecklistLog $log) => $this->deleteChecklistLog($log));
        $this->softDeletePivot('eamo_checklist_schedule_user', 'checklist_schedule_id', $schedule->id);
        $schedule->delete();
    }

    public function deleteChecklistLog(ChecklistLog $log): void
    {
        $this->softDeletePivot('eamo_checklist_log_users', 'checklist_log_id', $log->id);
        $log->delete();
    }

    public function deleteMaintenancePlan(MaintenancePlan $plan): void
    {
        $plan->maintenanceSchedule()->get()->each(fn (MaintenanceSchedule $schedule) => $this->deleteMaintenanceSchedule($schedule));
        $this->softDeletePivot('eamo_maintenance_plan_user', 'maintenance_plan_id', $plan->id);
        $plan->delete();
    }

    public function deleteMaintenanceCategory(MaintenanceCategory $category): void
    {
        $category->maintenanceItems()->get()->each(fn (MaintenanceItem $item) => $this->deleteMaintenanceItem($item));
        $category->delete();
    }

    public function deleteMaintenanceItem(MaintenanceItem $item): void
    {
        MaintenanceSchedule::query()
            ->where('maintenance_item_id', $item->id)
            ->get()
            ->each(fn (MaintenanceSchedule $schedule) => $this->deleteMaintenanceSchedule($schedule));

        $this->softDeletePivot('eamo_maintenance_item_user', 'maintenance_item_id', (string) $item->getKey());
        $item->delete();
    }

    public function deleteMaintenanceSchedules(Collection $schedules): void
    {
        $schedules->each(fn (MaintenanceSchedule $schedule) => $this->deleteMaintenanceSchedule($schedule));
    }

    public function deleteMaintenanceSchedule(MaintenanceSchedule $schedule): void
    {
        $schedule->maintenanceLogs()->get()->each(fn (MaintenanceLog $log) => $log->delete());
        $this->softDeletePivot('eamo_maintenance_schedule_user', 'maintenance_schedule_id', $schedule->id);
        $schedule->delete();
    }

    public function deleteErrorLog(EquipmentErrorLog $log): void
    {
        $this->softDeletePivot('eamo_equipment_error_log_user', 'error_log_id', $log->id);
        $log->delete();
    }

    private function softDeletePivot(string $table, string $foreignKey, string $id): void
    {
        DB::table($table)
            ->where($foreignKey, $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }
}
