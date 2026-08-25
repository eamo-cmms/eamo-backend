<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Masterdata\Equipment\Models\EquipmentCategory;

class EquipmentCategoryPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment_category.manage');
    }

    public function update(User $user, ?EquipmentCategory $equipmentCategory = null): bool
    {
        return $user->hasPermission('equipment_category.manage');
    }

    public function delete(User $user, ?EquipmentCategory $equipmentCategory = null): bool
    {
        return $user->hasPermission('equipment_category.manage');
    }
}
