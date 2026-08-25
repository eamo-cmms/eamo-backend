<?php

declare(strict_types=1);

namespace Modules\Equipment\ParameterLog\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\ParameterLog\Models\EquipmentParameterLog;

class EquipmentParameterLogPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment_parameter_log.create');
    }

    public function update(User $user, ?EquipmentParameterLog $log = null): bool
    {
        return $user->hasPermission('equipment_parameter_log.update');
    }

    public function delete(User $user, ?EquipmentParameterLog $log = null): bool
    {
        return $user->hasPermission('equipment_parameter_log.delete');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('equipment_parameter_log.import');
    }

    public function save(User $user): bool
    {
        return $user->hasPermission('equipment_parameter_log.save');
    }
}
