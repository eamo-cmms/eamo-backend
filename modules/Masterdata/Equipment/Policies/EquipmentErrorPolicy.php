<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Masterdata\Equipment\Models\EquipmentError;

class EquipmentErrorPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment_error.manage');
    }

    public function update(User $user, ?EquipmentError $equipmentError = null): bool
    {
        return $user->hasPermission('equipment_error.manage');
    }

    public function delete(User $user, ?EquipmentError $equipmentError = null): bool
    {
        return $user->hasPermission('equipment_error.manage');
    }
}
