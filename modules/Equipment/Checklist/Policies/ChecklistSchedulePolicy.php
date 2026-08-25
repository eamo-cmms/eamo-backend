<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Checklist\Models\ChecklistSchedule;

class ChecklistSchedulePolicy extends BasePolicy
{
    /**
     * Determine whether the user can complete the checklist schedule.
     */
    public function complete(User $user, ?ChecklistSchedule $schedule = null): bool
    {
        if (! $user->hasPermission('checklist_schedule.complete')) {
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
                || ($schedule->checklistSession && $schedule->checklistSession->users()->where('users.id', $user->id)->exists());
        }

        return false;
    }

    /**
     * Determine whether the user can delete daily checklist schedules.
     */
    public function deleteDaily(User $user): bool
    {
        return $user->hasPermission('checklist_schedule.delete_daily');
    }
}
