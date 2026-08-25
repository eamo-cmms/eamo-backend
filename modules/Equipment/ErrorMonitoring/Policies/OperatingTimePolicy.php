<?php

declare(strict_types=1);

namespace Modules\Equipment\ErrorMonitoring\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\ErrorMonitoring\Models\OperatingTime;

class OperatingTimePolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('operating_time.create');
    }

    public function update(User $user, ?OperatingTime $operatingTime = null): bool
    {
        return $user->hasPermission('operating_time.update');
    }

    public function delete(User $user, ?OperatingTime $operatingTime = null): bool
    {
        return $user->hasPermission('operating_time.delete');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('operating_time.import');
    }
}
