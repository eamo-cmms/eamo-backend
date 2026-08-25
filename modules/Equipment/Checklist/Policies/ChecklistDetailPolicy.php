<?php

declare(strict_types=1);

namespace Modules\Equipment\Checklist\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Modules\Equipment\Checklist\Models\ChecklistDetail;

class ChecklistDetailPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('checklist_detail.manage');
    }

    public function update(User $user, ?ChecklistDetail $detail = null): bool
    {
        return $user->hasPermission('checklist_detail.manage');
    }

    public function delete(User $user, ?ChecklistDetail $detail = null): bool
    {
        return $user->hasPermission('checklist_detail.manage');
    }
}
