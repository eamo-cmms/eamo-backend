<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Maintenance\Models\MaintenanceItem;

class MaintenanceItemPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('maintenance_item.manage');
    }

    public function update(User $user, ?MaintenanceItem $item = null): bool
    {
        return $user->hasPermission('maintenance_item.manage');
    }

    public function delete(User $user, ?MaintenanceItem $item = null): bool
    {
        return $user->hasPermission('maintenance_item.manage');
    }
}
