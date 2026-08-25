<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Masterdata\Equipment\Models\Equipment;

class EquipmentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can create equipment.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment.create');
    }

    /**
     * Determine whether the user can update the equipment.
     */
    public function update(User $user, ?Equipment $equipment = null): bool
    {
        return $user->hasPermission('equipment.update');
    }

    /**
     * Determine whether the user can delete the equipment.
     */
    public function delete(User $user, ?Equipment $equipment = null): bool
    {
        return $user->hasPermission('equipment.delete');
    }

    /**
     * Determine whether the user can mark last maintenance on the equipment.
     */
    public function markLastMaintenance(User $user, ?Equipment $equipment = null): bool
    {
        return $user->hasPermission('equipment.mark_maintenance');
    }

    /**
     * Determine whether the user can update parent equipment.
     */
    public function updateParent(User $user, ?Equipment $equipment = null): bool
    {
        return $user->hasPermission('equipment.update_parent');
    }

    /**
     * Determine whether the user can update equipment errors.
     */
    public function updateErrors(User $user, ?Equipment $equipment = null): bool
    {
        return $user->hasPermission('equipment.update_errors');
    }
}
