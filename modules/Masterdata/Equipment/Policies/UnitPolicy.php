<?php

declare(strict_types=1);

namespace Modules\Masterdata\Equipment\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Masterdata\Equipment\Models\Unit;

class UnitPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('unit.manage');
    }

    public function update(User $user, ?Unit $unit = null): bool
    {
        return $user->hasPermission('unit.manage');
    }

    public function delete(User $user, ?Unit $unit = null): bool
    {
        return $user->hasPermission('unit.manage');
    }
}
