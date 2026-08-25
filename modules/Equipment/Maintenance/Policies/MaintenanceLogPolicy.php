<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Maintenance\Models\MaintenanceLog;

class MaintenanceLogPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('maintenance_log.create');
    }

    public function update(User $user, ?MaintenanceLog $log = null): bool
    {
        return $user->hasPermission('maintenance_log.update');
    }

    public function delete(User $user, ?MaintenanceLog $log = null): bool
    {
        return $user->hasPermission('maintenance_log.delete');
    }
}
