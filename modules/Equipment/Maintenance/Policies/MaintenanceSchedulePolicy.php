<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Maintenance\Models\MaintenanceSchedule;

class MaintenanceSchedulePolicy extends BasePolicy
{
    public function complete(User $user, ?MaintenanceSchedule $schedule = null): bool
    {
        if (! $user->hasPermission('maintenance_schedule.complete')) {
            return false;
        }

        if ($user->hasRole(UserRole::Manager)) {
            return true;
        }

        if ($user->hasRole(UserRole::Engineer)) {
            if ($schedule === null) {
                return true;
            }

            return $schedule->users()->where('users.id', $user->id)->exists()
                || ($schedule->maintenancePlan && $schedule->maintenancePlan->users()->where('users.id', $user->id)->exists());
        }

        return false;
    }

    public function update(User $user, ?MaintenanceSchedule $schedule = null): bool
    {
        return $user->hasPermission('maintenance_schedule.update');
    }

    public function delete(User $user, ?MaintenanceSchedule $schedule = null): bool
    {
        return $user->hasPermission('maintenance_schedule.delete');
    }
}
