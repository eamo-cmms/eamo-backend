<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\ErrorMonitoring\Models\EquipmentErrorLog;

class EquipmentErrorLogPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('equipment_error_log.create');
    }

    public function update(User $user, ?EquipmentErrorLog $errorLog = null): bool
    {
        return $user->hasPermission('equipment_error_log.update');
    }

    public function delete(User $user, ?EquipmentErrorLog $errorLog = null): bool
    {
        return $user->hasPermission('equipment_error_log.delete');
    }
}
