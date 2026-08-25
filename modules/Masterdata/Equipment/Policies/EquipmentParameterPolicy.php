<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Masterdata\Equipment\Models\EquipmentParameter;

class EquipmentParameterPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment_parameter.manage');
    }

    public function update(User $user, ?EquipmentParameter $equipmentParameter = null): bool
    {
        return $user->hasPermission('equipment_parameter.manage');
    }

    public function delete(User $user, ?EquipmentParameter $equipmentParameter = null): bool
    {
        return $user->hasPermission('equipment_parameter.manage');
    }
}
