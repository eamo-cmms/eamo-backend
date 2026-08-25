<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Masterdata\Equipment\Models\EquipmentState;

class EquipmentStatePolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment_state.manage');
    }

    public function update(User $user, ?EquipmentState $equipmentState = null): bool
    {
        return $user->hasPermission('equipment_state.manage');
    }

    public function delete(User $user, ?EquipmentState $equipmentState = null): bool
    {
        return $user->hasPermission('equipment_state.manage');
    }
}
