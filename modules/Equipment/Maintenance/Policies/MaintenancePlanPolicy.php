<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Maintenance\Models\MaintenancePlan;

class MaintenancePlanPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('maintenance_plan.create');
    }

    public function update(User $user, ?MaintenancePlan $plan = null): bool
    {
        return $user->hasPermission('maintenance_plan.update');
    }

    public function delete(User $user, ?MaintenancePlan $plan = null): bool
    {
        return $user->hasPermission('maintenance_plan.delete');
    }

    public function judge(User $user, ?MaintenancePlan $plan = null): bool
    {
        if (! $user->hasPermission('maintenance_plan.judge')) {
            return false;
        }

        if ($user->hasRole(UserRole::Manager)) {
            return true;
        }

        if ($user->hasRole(UserRole::Engineer)) {
            if ($plan === null) {
                return true;
            }

            return $plan->users()->where('users.id', $user->id)->exists();
        }

        return false;
    }
}
