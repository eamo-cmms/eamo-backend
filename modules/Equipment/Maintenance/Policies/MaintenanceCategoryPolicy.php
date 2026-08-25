<?php

declare(strict_types=1);

namespace Modules\Equipment\Maintenance\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Maintenance\Models\MaintenanceCategory;

class MaintenanceCategoryPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('maintenance_category.manage');
    }

    public function update(User $user, ?MaintenanceCategory $category = null): bool
    {
        return $user->hasPermission('maintenance_category.manage');
    }

    public function delete(User $user, ?MaintenanceCategory $category = null): bool
    {
        return $user->hasPermission('maintenance_category.manage');
    }
}
